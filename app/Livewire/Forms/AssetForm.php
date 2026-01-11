<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Asset;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AssetForm extends Form
{
    // Simpan Model Aset yang sedang diedit (nullable)
    public ?Asset $assetModel = null;

    // ==========================================
    // PROPERTIES
    // ==========================================

    public $asset_tag = ''; 
    public $serial = '';
    public $image; 
    public $asset_model_id = '';
    public $asset_status_id = '';
    public $location_id = ''; 
    public $supplier_id = '';
    public $assigned_employee_id = ''; 
    public $order_number = '';
    public $purchase_date = '';
    public $purchase_cost = '';
    public $warranty_months = '';
    public $eol_date = '';

    // ==========================================
    // RULES (VALIDASI)
    // ==========================================
    public function rules()
    {
        return [
            // KUNCI UTAMA: 'nullable'. 
            // Artinya user boleh mengosongkan form ini.
            // Jika kosong -> Model akan generate otomatis.
            // Jika diisi -> Validasi 'size:10' dan 'unique' akan berjalan.
            'asset_tag' => [
                'nullable', 
                'string', 
                'size:10', 
                Rule::unique('assets', 'asset_tag')->ignore($this->assetModel?->id)
            ],

            'image' => 'nullable|image|max:2048|mimes:jpg,jpeg,png', 
            'asset_model_id' => 'required|exists:asset_models,id',
            'asset_status_id' => 'required|exists:asset_statuses,id',
            'location_id' => 'required|exists:locations,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'assigned_employee_id' => 'nullable|exists:employees,id',
            'serial' => 'nullable|string|max:255',
            'order_number' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0', 
            'warranty_months' => 'nullable|integer|min:0',
            'eol_date' => 'nullable|date|after_or_equal:purchase_date',
        ];
    }

    // ==========================================
    // PESAN ERROR (CUSTOM INDONESIA)
    // ==========================================
    public function messages() 
    {
        return [
            // Pesan 'required' dihapus karena sekarang boleh kosong
            'asset_tag.size' => 'Jika diisi manual, Kode aset harus tepat 10 karakter.',
            'asset_tag.unique' => 'Kode aset ini sudah digunakan oleh aset lain.',
            
            'asset_model_id.required' => 'Model/Perangkat wajib dipilih.',
            'asset_model_id.exists' => 'Model yang dipilih tidak valid.',
            'asset_status_id.required' => 'Status aset wajib dipilih.',
            'location_id.required' => 'Lokasi penempatan wajib dipilih.',
            'purchase_cost.numeric' => 'Harga beli harus berupa angka.',
            'purchase_cost.min' => 'Harga beli tidak boleh negatif.',
            'warranty_months.integer' => 'Garansi harus berupa angka bulat (bulan).',
            'eol_date.after_or_equal' => 'Tanggal habis masa pakai tidak boleh sebelum tanggal beli.',
            'image.image' => 'File harus berupa gambar.',
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 2MB.',
            'image.mimes' => 'Format gambar harus jpg, jpeg, atau png.',
        ];
    }

    // ==========================================
    // ACTIONS
    // ==========================================

    public function setAsset(Asset $asset)
    {
        $this->assetModel = $asset;

        $this->asset_tag = $asset->asset_tag;
        $this->serial = $asset->serial;
        $this->asset_model_id = $asset->asset_model_id;
        $this->asset_status_id = $asset->asset_status_id;
        $this->location_id = $asset->location_id;
        $this->supplier_id = $asset->supplier_id;
        
        if ($asset->assigned_to_type === 'App\Models\Employee') {
            $this->assigned_employee_id = $asset->assigned_to_id;
        }

        $this->order_number = $asset->order_number;
        $this->purchase_date = $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '';
        $this->purchase_cost = $asset->purchase_cost;
        $this->warranty_months = $asset->warranty_months;
        $this->eol_date = $asset->eol_date ? $asset->eol_date->format('Y-m-d') : '';
    }

    public function store()
    {
        $this->validate();
        
        // Ambil data yang sudah dibersihkan (string kosong jadi null)
        $data = $this->prepareData();

        // Upload gambar jika ada
        if ($this->image) {
            $data['image'] = $this->image->store('assets', 'public');
        }

        // SIMPAN DATA
        // Karena di prepareData() asset_tag yang kosong sudah diubah jadi NULL,
        // Maka Model Asset (Event creating) akan mendeteksinya dan membuatkan tag otomatis.
        Asset::create($data);
        
        $this->reset();
    }

    public function update()
    {
        $this->validate(); 
        $data = $this->prepareData();

        // Hapus 'asset_tag' agar tidak bisa diubah saat edit
        unset($data['asset_tag']); 

        // Handle Image Update
        if ($this->image) {
            if ($this->assetModel->image) {
                Storage::disk('public')->delete($this->assetModel->image);
            }
            $data['image'] = $this->image->store('assets', 'public');
        }

        $this->assetModel->update($data);
    }

    protected function prepareData()
    {
        // 1. Buang field yang tidak masuk database langsung
        $data = $this->except(['image', 'assetModel', 'assigned_employee_id']);

        // 2. Ubah string kosong jadi NULL
        $nullableFields = [
            'supplier_id', 'purchase_date', 'eol_date', 
            'purchase_cost', 'warranty_months', 'order_number', 
            'serial', 'asset_tag'
        ];
        
        foreach ($nullableFields as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = $data[$field] === '' ? null : $data[$field];
            }
        }

        // 3. LOGIKA POLYMORPHIC (PERBAIKAN PERMANEN DISINI)
        // Default: Set null
        $data['assigned_to_type'] = null;
        $data['assigned_to_id'] = null;

        // Jika user memilih karyawan
        if (!empty($this->assigned_employee_id)) {
            $data['assigned_to_id'] = $this->assigned_employee_id;
            
            // --- PERUBAHAN UTAMA ---
            // JANGAN PAKAI STRING MANUAL: 'App\Models\Employee'
            // PAKAI CARA INI AGAR 100% AKURAT SESUAI NAMESPACE ASLI:
            $data['assigned_to_type'] = \App\Models\Employee::class; 
        }

        return $data;
    }
}