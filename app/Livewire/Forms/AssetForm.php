<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Asset;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

/**
 * Class AssetForm
 *
 * Objek Form Livewire yang menangani validasi dan manipulasi data untuk entitas Aset.
 * Mengelola proses sanitasi input, upload gambar, serta logika relasi polimorfik
 * sebelum data disimpan ke database.
 *
 * @package App\Livewire\Forms
 */
class AssetForm extends Form
{
    /** @var Asset|null Model aset yang sedang diedit (jika ada) */
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
    // VALIDATION & MESSAGES
    // ==========================================

    /**
     * Mendefinisikan aturan validasi untuk properti form.
     * Mengizinkan 'asset_tag' kosong agar dapat digenerate otomatis oleh Model.
     *
     * @return array
     */
    public function rules()
    {
        return [
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

    /**
     * Menyediakan pesan error kustom dalam Bahasa Indonesia.
     *
     * @return array
     */
    public function messages() 
    {
        return [
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

    /**
     * Mengisi properti form dengan data dari model Aset yang ada.
     * Digunakan saat inisialisasi mode Edit.
     *
     * @param Asset $asset
     */
    public function setAsset(Asset $asset)
    {
        // 1. Set Model Reference
        $this->assetModel = $asset;

        // 2. Mapping data dasar
        $this->asset_tag = $asset->asset_tag;
        $this->serial = $asset->serial;
        $this->asset_model_id = $asset->asset_model_id;
        $this->asset_status_id = $asset->asset_status_id;
        $this->location_id = $asset->location_id;
        $this->supplier_id = $asset->supplier_id;
        
        // 3. Mapping relasi polimorfik (jika aset ditugaskan ke karyawan)
        if ($asset->assigned_to_type === \App\Models\Employee::class) {
            $this->assigned_employee_id = $asset->assigned_to_id;
        }

        // 4. Formatting data tanggal dan angka
        $this->order_number = $asset->order_number;
        $this->purchase_date = $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '';
        $this->purchase_cost = $asset->purchase_cost;
        $this->warranty_months = $asset->warranty_months;
        $this->eol_date = $asset->eol_date ? $asset->eol_date->format('Y-m-d') : '';
    }

    /**
     * Menyimpan data aset baru ke database.
     */
    public function store()
    {
        // 1. Validasi Input
        $this->validate();
        
        // 2. Sanitasi & Persiapan Data
        $data = $this->prepareData();

        // 3. Proses Upload Gambar (Jika ada)
        if ($this->image) {
            $data['image'] = $this->image->store('assets', 'public');
        }

        // 4. Simpan ke Database
        // Catatan: Jika asset_tag null, model akan men-generate tag otomatis via event creating.
        Asset::create($data);
        
        // 5. Reset state form
        $this->reset();
    }

    /**
     * Memperbarui data aset yang sudah ada.
     */
    public function update()
    {
        // 1. Validasi Input
        $this->validate(); 

        // 2. Sanitasi & Persiapan Data
        $data = $this->prepareData();

        // 3. Proteksi Data: Hapus asset_tag dari payload agar tidak berubah
        unset($data['asset_tag']); 

        // 4. Manajemen File Gambar (Hapus lama, simpan baru)
        if ($this->image) {
            if ($this->assetModel->image) {
                Storage::disk('public')->delete($this->assetModel->image);
            }
            $data['image'] = $this->image->store('assets', 'public');
        }

        // 5. Eksekusi Update
        $this->assetModel->update($data);
    }

    /**
     * Membersihkan dan mempersiapkan data sebelum disimpan.
     * Mengubah string kosong menjadi NULL dan menangani relasi polimorfik.
     *
     * @return array
     */
    protected function prepareData()
    {
        // 1. Filter Field: Hapus properti yang bukan kolom database langsung
        $data = $this->except(['image', 'assetModel', 'assigned_employee_id']);

        // 2. Normalisasi Data: Ubah string kosong ('') menjadi NULL untuk kolom nullable
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

        // 3. Penanganan Relasi Polimorfik (Assigned To)
        // Default reset ke null
        $data['assigned_to_type'] = null;
        $data['assigned_to_id'] = null;

        // Jika ID karyawan diisi, set tipe polimorfik ke Class Employee
        if (!empty($this->assigned_employee_id)) {
            $data['assigned_to_id'] = $this->assigned_employee_id;
            $data['assigned_to_type'] = \App\Models\Employee::class; 
        }

        return $data;
    }
}