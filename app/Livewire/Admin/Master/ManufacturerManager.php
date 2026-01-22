<?php

namespace App\Livewire\Admin\Master;

use App\Models\Manufacturer;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

#[Layout('components.layouts.admin')]
#[Title('Kelola Pabrikan')]
class ManufacturerManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES
    // ==========================================

    public $manufacturerId;
    public $name = '';
    public $url = '';
    public $support_url = '';
    public $support_phone = '';
    public $support_email = '';

    // Image Handling
    public $newImage;
    public $oldImage;

    // UI State
    public $isEditMode = false;
    public $deleteName = ''; // Untuk konfirmasi hapus

    public $search = '';

    // ==========================================
    // VALIDATION RULES
    // ==========================================

    protected function rules()
    {
        return [
            'name' => [
                'required', 'string', 'max:255', 
                Rule::unique('manufacturers', 'name')->ignore($this->manufacturerId)
            ],
            'url' => 'nullable|url|max:255',
            'support_url' => 'nullable|url|max:255',
            'support_phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\(\)\s]*$/'],
            'support_email' => 'nullable|email|max:255',
            'newImage' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama pabrikan wajib diisi.',
        'name.unique' => 'Nama pabrikan ini sudah terdaftar.',
        'url.url' => 'Format URL Website tidak valid.',
        'support_url.url' => 'Format URL Support tidak valid.',
        'support_phone.regex' => 'Nomor telepon hanya boleh berisi angka dan simbol (+ - ( )).',
        'support_email.email' => 'Format email tidak valid.',
        'newImage.max' => 'Ukuran gambar maksimal 10MB.',
        'newImage.image' => 'File harus berupa gambar.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // ==========================================
    // ACTIONS (Server-Side Logic)
    // ==========================================

    // 1. Persiapan Create
    public function create()
    {
        $this->resetInputFields();
        $this->dispatch('open-modal-form');
    }

    // 2. Persiapan Edit
    public function edit($id)
    {
        $data = Manufacturer::find($id);
        if (!$data) return;

        $this->manufacturerId = $data->id;
        $this->name = $data->name;
        $this->url = $data->url;
        $this->support_url = $data->support_url;
        $this->support_phone = $data->support_phone;
        $this->support_email = $data->support_email;
        $this->oldImage = $data->image;
        $this->newImage = null; // Reset upload baru

        $this->isEditMode = true;
        $this->resetValidation();

        $this->dispatch('open-modal-form');
    }

    // 3. Persiapan Hapus
    public function confirmDelete($id)
    {
        $data = Manufacturer::find($id);
        if (!$data) return;

        $this->manufacturerId = $data->id;
        $this->deleteName = $data->name;

        $this->dispatch('open-modal-delete');
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->url = '';
        $this->support_url = '';
        $this->support_phone = '';
        $this->support_email = '';
        $this->newImage = null;
        $this->oldImage = null;
        
        $this->manufacturerId = null;
        $this->deleteName = '';
        $this->isEditMode = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function store()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'url' => $this->url,
            'support_url' => $this->support_url,
            'support_phone' => $this->support_phone,
            'support_email' => $this->support_email,
        ];

        // Logic Image Upload
        if ($this->newImage) {
            if ($this->isEditMode && $this->oldImage) {
                if(Storage::disk('public')->exists($this->oldImage)) {
                    Storage::disk('public')->delete($this->oldImage);
                }
            }
            $data['image'] = $this->compressAndStore($this->newImage);
        }

        if ($this->isEditMode && $this->manufacturerId) {
            Manufacturer::findOrFail($this->manufacturerId)->update($data);
            $message = 'Data pabrikan berhasil diperbarui.';
        } else {
            Manufacturer::create($data);
            $message = 'Data pabrikan berhasil ditambahkan.';
        }

        session()->flash('message', $message);
        
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function delete()
    {
        if (!$this->manufacturerId) return;

        try {
            $data = Manufacturer::findOrFail($this->manufacturerId);
            
            if ($data->image && Storage::disk('public')->exists($data->image)) {
                Storage::disk('public')->delete($data->image);
            }

            $data->delete();
            session()->flash('message', 'Data pabrikan berhasil dihapus.');

        } catch (\Exception $e) {
            session()->flash('error', 'Gagal menghapus data. Kemungkinan data sedang digunakan pada Model Aset.');
        }
        
        // Tutup modal apapun hasilnya
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    // ==========================================
    // HELPERS
    // ==========================================

    protected function compressAndStore($file)
    {
        $filename = md5($file->getClientOriginalName() . microtime()) . '.jpg';
        $path = 'manufacturers/' . $filename; 

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            $image->scaleDown(width: 1200);
            $encoded = $image->toJpeg(quality: 80);
            Storage::disk('public')->put($path, $encoded);
        } catch (\Exception $e) {
            $path = $file->storeAs('manufacturers', $filename, 'public');
        }

        return $path;
    }

    public function render()
    {
        $manufacturers = Manufacturer::query()
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('support_email', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.master.manufacturer-manager', [
            'manufacturers' => $manufacturers
        ]);
    }
}