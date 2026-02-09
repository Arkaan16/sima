<?php

namespace App\Livewire\Master;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

#[Title('Kelola Supplier')]
class Suppliers extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES
    // ==========================================

    public $supplierId;
    public $name = '';
    public $contact_name = '';
    public $email = '';
    public $phone = '';
    public $address = '';
    public $url = '';

    // Image Handling
    public $newImage;
    public $oldImage;

    // UI State
    public $isEditMode = false;
    public $deleteName = ''; // Untuk nama di konfirmasi hapus

    public $search = '';

    protected $queryString = ['search' => ['except' => '']];

    // ==========================================
    // VALIDATION RULES
    // ==========================================

    protected function rules()
    {
        return [
            'name' => [
                'required', 'string', 'max:255', 
                Rule::unique('suppliers', 'name')->ignore($this->supplierId)
            ],
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\(\)\s]*$/'],
            'address' => 'nullable|string|max:1000',
            'url' => 'nullable|url|max:255',
            'newImage' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240', 
        ];
    }

    protected $messages = [
        'name.required' => 'Nama supplier wajib diisi.',
        'name.unique' => 'Supplier ini sudah terdaftar.',
        'email.email' => 'Format email tidak valid.',
        'url.url' => 'Format URL harus valid (awali dengan http:// atau https://).',
        'phone.regex' => 'Nomor telepon hanya boleh berisi angka dan simbol (+ - ( )).',
        'newImage.image' => 'File harus berupa gambar.',
        'newImage.max' => 'Ukuran gambar maksimal 10MB.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // ==========================================
    // ACTIONS (Server-Side Logic)
    // ==========================================

    // 1. Create
    public function create()
    {
        $this->resetInputFields();
        $this->dispatch('open-modal-form');
    }

    // 2. Edit
    public function edit($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) return;

        $this->supplierId = $supplier->id;
        $this->name = $supplier->name;
        $this->contact_name = $supplier->contact_name;
        $this->email = $supplier->email;
        $this->phone = $supplier->phone;
        $this->address = $supplier->address;
        $this->url = $supplier->url;
        $this->oldImage = $supplier->image;
        $this->newImage = null; // Reset input baru

        $this->isEditMode = true;
        $this->resetValidation();

        $this->dispatch('open-modal-form');
    }

    // 3. Confirm Delete
    public function confirmDelete($id)
    {
        $supplier = Supplier::find($id);
        if (!$supplier) return;

        $this->supplierId = $supplier->id;
        $this->deleteName = $supplier->name;

        $this->dispatch('open-modal-delete');
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->contact_name = '';
        $this->email = '';
        $this->phone = '';
        $this->address = '';
        $this->url = '';
        $this->newImage = null;
        $this->oldImage = null;
        $this->supplierId = null;
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
            'contact_name' => $this->contact_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'url' => $this->url,
        ];

        // Image Logic
        if ($this->newImage) {
            if ($this->isEditMode && $this->oldImage) {
                if(Storage::disk('public')->exists($this->oldImage)) {
                    Storage::disk('public')->delete($this->oldImage);
                }
            }
            $data['image'] = $this->compressAndStore($this->newImage);
        }

        if ($this->isEditMode && $this->supplierId) {
            Supplier::findOrFail($this->supplierId)->update($data);
            $message = 'Data pemasok berhasil diperbarui.';
        } else {
            Supplier::create($data);
            $message = 'Pemasok baru berhasil ditambahkan.';
        }

        session()->flash('message', $message);
        
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    public function delete()
    {
        if (!$this->supplierId) return;

        try {
            $supplier = Supplier::findOrFail($this->supplierId);
            
            if ($supplier->image && Storage::disk('public')->exists($supplier->image)) {
                Storage::disk('public')->delete($supplier->image);
            }

            $supplier->delete();
            session()->flash('message', 'Data pemasok berhasil dihapus.');

        } catch (QueryException $e) {
            if ($e->getCode() == 23000) { 
                session()->flash('error', 'Gagal menghapus: Pemasok ini sedang digunakan oleh data aset/stok.');
            } else {
                session()->flash('error', 'Terjadi kesalahan database saat menghapus data.');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Terjadi kesalahan sistem.');
        }
        
        // Tutup modal apapun hasilnya
        $this->dispatch('close-all-modals');
        $this->resetInputFields();
    }

    protected function compressAndStore($file)
    {
        $filename = md5($file->getClientOriginalName() . microtime()) . '.jpg';
        $path = 'suppliers/' . $filename; 

        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());
            $image->scaleDown(width: 1200);
            $encoded = $image->toJpeg(quality: 80);
            Storage::disk('public')->put($path, $encoded);
        } catch (\Exception $e) {
            $path = $file->storeAs('suppliers', $filename, 'public');
        }

        return $path;
    }

    public function render()
    {
        $suppliers = Supplier::query()
            ->when($this->search, function($q) {
                $q->where(function($sub) {
                    $sub->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('contact_name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.master.suppliers', [
            'suppliers' => $suppliers
        ]);
    }
}