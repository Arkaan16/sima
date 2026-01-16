<?php

namespace App\Livewire\Admin\Master;

use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Illuminate\Validation\Rule;

/**
 * Class LocationManager
 *
 * Komponen Livewire untuk menangani manajemen data Master Lokasi (Gedung & Ruangan).
 * Mengelola operasi CRUD dengan dukungan hierarki parent-child (Gedung sebagai Parent,
 * Ruangan sebagai Child) serta validasi integritas data.
 *
 * @package App\Livewire\Admin\Master
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Lokasi')]
class LocationManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    // ==========================================
    // PROPERTIES
    // ==========================================

    /** @var int|null Primary Key dari lokasi yang sedang diedit atau dihapus */
    public $locationId;

    /** @var string Nama lokasi (Model Binding) */
    public $name;

    /** @var int|null Foreign Key untuk lokasi induk (Gedung) */
    public $parent_location_id = null;

    // --- Dropdown Search State ---
    public $parentSearch = '';
    public $selectedParentName = '';

    /** @var string Kata kunci pencarian global pada tabel */
    #[Url(except: '')]
    public $search = '';

    // --- UI State Flags ---
    public $showFormModal = false;
    public $showDeleteModal = false;
    public $isEditMode = false;

    // ==========================================
    // VALIDATION
    // ==========================================

    /**
     * Mendefinisikan aturan validasi input.
     * Memastikan nama lokasi unik dalam lingkup parent yang sama.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'parent_location_id' => 'nullable|exists:locations,id',
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('locations', 'name')
                    ->where('parent_location_id', $this->parent_location_id)
                    ->ignore($this->locationId),
            ],
        ];
    }

    /**
     * Menyediakan pesan error kustom untuk validasi.
     *
     * @var array
     */
    protected $messages = [
        'name.required' => 'Nama lokasi/ruangan wajib diisi.',
        'name.unique' => 'Nama ruangan ini sudah ada di Gedung tersebut.',
    ];

    // ==========================================
    // RENDER & SEARCH LOGIC
    // ==========================================

    /**
     * Hook lifecycle: Mereset pagination saat kata kunci pencarian berubah.
     */
    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * Computed Property: Mengambil daftar kandidat Parent (Gedung).
     * Memfilter hanya lokasi level root untuk mencegah hierarki bertingkat dalam.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getParentsProperty()
    {
        return Location::query()
            ->when($this->parentSearch, fn($q) => $q->where('name', 'like', '%' . $this->parentSearch . '%'))
            ->whereNull('parent_location_id')
            ->limit(5)
            ->get();
    }

    /**
     * Mengatur state saat user memilih parent dari dropdown.
     *
     * @param int $id
     * @param string $name
     */
    public function selectParent($id, $name)
    {
        $this->parent_location_id = $id;
        $this->selectedParentName = $name;
        $this->parentSearch = '';
    }

    /**
     * Menghapus pilihan parent (menjadikan lokasi sebagai Root/Gedung).
     */
    public function clearParent()
    {
        $this->parent_location_id = null;
        $this->selectedParentName = '';
        $this->parentSearch = '';
    }

    /**
     * Merender tampilan komponen utama.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // 1. Inisialisasi Query Builder
        $locations = Location::with('parent')
            // 2. Terapkan filter pencarian (Nama Lokasi atau Nama Parent)
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('parent', fn($s) => $s->where('name', 'like', '%' . $this->search . '%'));
            })
            // 3. Urutkan agar Child tampil berdekatan dengan Parent-nya
            ->orderByRaw('COALESCE(parent_location_id, id), id')
            // 4. Eksekusi Pagination
            ->paginate(10);

        return view('livewire.admin.master.location-manager', [
            'locations' => $locations
        ]);
    }

    // ==========================================
    // CRUD LOGIC
    // ==========================================

    /**
     * Mengembalikan seluruh state form ke nilai default.
     */
    public function resetInputFields()
    {
        $this->name = '';
        $this->locationId = null;
        $this->parent_location_id = null;
        $this->parentSearch = '';
        $this->selectedParentName = '';
        $this->isEditMode = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    /**
     * Membuka modal untuk pembuatan data baru.
     */
    public function create()
    {
        $this->resetInputFields();
        $this->showFormModal = true;
    }

    /**
     * Membuka modal edit dan mengisi form dengan data yang ada.
     *
     * @param int $id
     */
    public function edit($id)
    {
        $this->resetValidation();
        
        // 1. Ambil data dari database
        $loc = Location::with('parent')->findOrFail($id);

        // 2. Isi state properti
        $this->locationId = $id;
        $this->name = $loc->name;
        $this->parent_location_id = $loc->parent_location_id;
        $this->selectedParentName = $loc->parent ? $loc->parent->name : '';

        // 3. Tampilkan modal
        $this->isEditMode = true;
        $this->showFormModal = true;
    }

    /**
     * Menangani proses penyimpanan data (Create & Update).
     */
    public function store()
    {
        // 1. Validasi Input sesuai rules()
        $this->validate();

        // 2. Cek Logika Bisnis: Validasi hierarki (Self-parenting check)
        if ($this->isEditMode && $this->locationId == $this->parent_location_id) {
             $this->addError('parent_location_id', 'Lokasi tidak bisa menjadi induk bagi dirinya sendiri.');
             return;
        }

        // 3. Persiapkan payload data
        $data = [
            'name' => $this->name,
            'parent_location_id' => $this->parent_location_id ?: null,
        ];

        // 4. Eksekusi Simpan ke Database
        if ($this->isEditMode && $this->locationId) {
            Location::findOrFail($this->locationId)->update($data);
            session()->flash('message', 'Data lokasi berhasil diperbarui.');
        } else {
            Location::create($data);
            session()->flash('message', 'Data lokasi berhasil ditambahkan.');
        }
        
        // 5. Tutup modal dan bersihkan state
        $this->closeModal();
    }

    /**
     * Menampilkan konfirmasi sebelum menghapus data.
     *
     * @param int $id
     */
    public function confirmDelete($id)
    {
        $this->locationId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Mengeksekusi penghapusan data dari database.
     */
    public function delete()
    {
        if ($this->locationId) {
            try {
                // 1. Cari data berdasarkan ID
                $loc = Location::findOrFail($this->locationId);
                
                // 2. Cek Logika Bisnis: Validasi ketergantungan (Child nodes)
                if ($loc->children()->count() > 0) {
                    session()->flash('error', 'Gagal! Gedung ini masih memiliki ruangan di dalamnya.');
                } else {
                    // 3. Hapus data jika aman
                    $loc->delete();
                    session()->flash('message', 'Lokasi berhasil dihapus.');
                }
            } catch (\Exception $e) {
                // 4. Tangani error constraint database (Foreign Key)
                session()->flash('error', 'Gagal menghapus. Lokasi sedang digunakan data aset.');
            }
        }
        
        // 5. Tutup modal
        $this->closeModal();
    }

    /**
     * Menutup semua modal aktif dan mereset form.
     */
    public function closeModal()
    {
        $this->showFormModal = false;
        $this->showDeleteModal = false;
        $this->resetInputFields();
    }
}