<?php

namespace App\Livewire\Admin\Master;

use App\Models\Supplier;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;

/**
 * Class SupplierManager
 * Menangani CRUD data Supplier dengan optimasi search dan validasi.
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Supplier')]
class SupplierManager extends Component
{
    use WithPagination;
    use WithFileUploads;

    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES
    // ==========================================

    public $supplierId;

    // Form Inputs
    public $name;
    public $contact_name;
    public $email;
    public $phone;
    public $address;
    public $url;

    // Gambar
    public $newImage;
    public $oldImage;

    // UI State
    public $search = '';
    public $showFormModal = false;
    public $showDeleteModal = false;
    public $isEditMode = false;

    // Update URL query string saat search berubah
    protected $queryString = ['search' => ['except' => '']];

    // ==========================================
    // VALIDASI
    // ==========================================

    protected function rules()
    {
        return [
            // Validasi Nama: Wajib, Unik (kecuali punya diri sendiri saat edit)
            'name' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('suppliers', 'name')->ignore($this->supplierId)
            ],
            
            // Validasi Kontak
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20', // Cukup string pendek
            'address' => 'nullable|string|max:1000',
            'url' => 'nullable|url|max:255', // Validasi format URL valid

            // Validasi Gambar
            'newImage' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', 
        ];
    }

    protected $messages = [
        'name.required' => 'Nama supplier wajib diisi.',
        'name.unique' => 'Supplier ini sudah terdaftar.',
        'email.email' => 'Format email tidak valid.',
        'url.url' => 'Format URL harus valid (diawali http:// atau https://).',
        'newImage.image' => 'File harus berupa gambar.',
        'newImage.max' => 'Ukuran gambar maksimal 2MB.',
    ];

    // ==========================================
    // LOGIKA
    // ==========================================

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        // Optimasi Query:
        // Tidak ada relasi (belongsTo) jadi tidak butuh eager loading (with).
        // Fokus pada grouping search query agar efisien.
        
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

        return view('livewire.admin.master.supplier-manager', [
            'suppliers' => $suppliers
        ]);
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
        $this->isEditMode = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function create()
    {
        $this->resetInputFields();
        $this->showFormModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $data = Supplier::findOrFail($id);

        $this->supplierId = $id;
        $this->name = $data->name;
        $this->contact_name = $data->contact_name;
        $this->email = $data->email;
        $this->phone = $data->phone;
        $this->address = $data->address;
        $this->url = $data->url;
        $this->oldImage = $data->image;

        $this->isEditMode = true;
        $this->showFormModal = true;
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

        // Handle Image Upload
        if ($this->newImage) {
            // Hapus gambar lama jika edit mode
            if ($this->isEditMode && $this->oldImage) {
                Storage::disk('public')->delete($this->oldImage);
            }
            $data['image'] = $this->newImage->store('suppliers', 'public');
        } else {
            // Pertahankan gambar lama jika edit
            if ($this->isEditMode) {
                $data['image'] = $this->oldImage;
            }
        }

        if ($this->isEditMode && $this->supplierId) {
            Supplier::findOrFail($this->supplierId)->update($data);
            session()->flash('message', 'Data pemasok berhasil diperbarui.');
        } else {
            Supplier::create($data);
            session()->flash('message', 'Pemasok baru berhasil ditambahkan.');
        }

        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->supplierId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->supplierId) {
            try {
                $supplier = Supplier::findOrFail($this->supplierId);
                
                if ($supplier->image) {
                    Storage::disk('public')->delete($supplier->image);
                }

                $supplier->delete();
                session()->flash('message', 'Data pemasok berhasil dihapus.');

            } catch (QueryException $e) {
                // Error Code 23000 = Integrity Constraint Violation (Foreign Key)
                if ($e->getCode() == 23000) {
                    session()->flash('error', 'Gagal menghapus: Pemasok ini sedang digunakan oleh data aset/stok.');
                } else {
                    session()->flash('error', 'Terjadi kesalahan database saat menghapus data.');
                }
            } catch (\Exception $e) {
                session()->flash('error', 'Terjadi kesalahan sistem.');
            }
        }
        $this->closeModal();
    }

    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->resetInputFields();
    }
}