<?php

namespace App\Livewire\Forms;

use Livewire\Form;
use App\Models\Asset;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
// Import Library Intervention Image
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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

    public function rules()
    {
        return [
            'asset_tag' => [
                'nullable', 
                'string', 
                'size:10', 
                'regex:/^[0-9]+$/', // WAJIB ANGKA SAJA
                Rule::unique('assets', 'asset_tag')->ignore($this->assetModel?->id)
            ],
            // UPDATE: Max 10MB (10240 KB)
            'image' => 'nullable|image|max:10240|mimes:jpg,jpeg,png,webp', 
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
            'eol_date' => [
                'nullable',
                'date',
                Rule::when($this->purchase_date, ['after_or_equal:purchase_date']),
            ],
        ];
    }

    public function messages() 
    {
        return [
            'asset_tag.regex' => 'Kode aset hanya boleh berisi angka (0-9).',
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
            // Update pesan error size
            'image.max' => 'Ukuran gambar tidak boleh lebih dari 10MB.',
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
        
        if ($asset->assigned_to_type === \App\Models\Employee::class) {
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
        
        $data = $this->prepareData();

        // IMPLEMENTASI COMPRESS SAAT STORE
        if ($this->image) {
            $data['image'] = $this->compressAndStore($this->image);
        }

        Asset::create($data);
        
        $this->reset();
    }

    public function update()
    {
        $this->validate(); 

        $data = $this->prepareData();

        unset($data['asset_tag']); 

        // IMPLEMENTASI COMPRESS SAAT UPDATE
        if ($this->image) {
            // Hapus gambar lama fisik
            if ($this->assetModel->image) {
                Storage::disk('public')->delete($this->assetModel->image);
            }
            // Simpan gambar baru yang sudah dikompres
            $data['image'] = $this->compressAndStore($this->image);
        }

        $this->assetModel->update($data);
    }

    protected function prepareData()
    {
        $data = $this->except(['image', 'assetModel', 'assigned_employee_id']);

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

        $data['assigned_to_type'] = null;
        $data['assigned_to_id'] = null;

        if (!empty($this->assigned_employee_id)) {
            $data['assigned_to_id'] = $this->assigned_employee_id;
            $data['assigned_to_type'] = \App\Models\Employee::class; 
        }

        return $data;
    }

    // ==========================================
    // HELPER: IMAGE COMPRESSION
    // ==========================================

    /**
     * Mengkompres gambar dan menyimpannya ke storage.
     * Mengubah ukuran (scale down) dan mengurangi kualitas JPEG.
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @return string Path file yang disimpan
     */
    protected function compressAndStore($file)
    {
        // 1. Generate nama file unik
        $filename = md5($file->getClientOriginalName() . microtime()) . '.jpg';
        $path = 'assets/' . $filename; 

        try {
            // 2. Inisialisasi Manager (Intervention Image v3)
            $manager = new ImageManager(new Driver());
            
            // 3. Baca File
            $image = $manager->read($file->getRealPath());
            
            // 4. Resize: Scale Down agar aspek rasio terjaga
            // Lebar maksimal 1000px (opsional, sesuaikan kebutuhan)
            $image->scaleDown(width: 1000);
            
            // 5. Encode ke JPG dengan kualitas 80%
            $encoded = $image->toJpeg(quality: 80);
            
            // 6. Simpan ke Storage Public
            Storage::disk('public')->put($path, (string) $encoded);

        } catch (\Exception $e) {
            // Fallback: Jika library error (misal driver GD tidak ada), 
            // simpan file asli seperti biasa tanpa kompresi.
            $path = $file->storeAs('assets', $filename, 'public');
        }

        return $path;
    }
}