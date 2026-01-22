<?php

namespace App\Livewire\Admin\Master;

use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.admin')]
#[Title('Kelola Lokasi')]
class LocationManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    // Properties Data
    public $locationId;
    public $name = '';
    public $parent_location_id = null;
    
    // Properties UI & Search
    public $isEditMode = false;
    public $deleteName = ''; 
    public $search = '';
    public $parentSearch = '';
    public $selectedParentName = '';

    // Validation Rules
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

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // --- ACTIONS ---

    // 1. Tombol Tambah Ditekan
    public function create()
    {
        $this->resetInputFields();
        // Kirim perintah ke browser: Buka Modal Form, Tutup yang lain
        $this->dispatch('open-modal-form'); 
    }

    // 2. Tombol Edit Ditekan
    public function edit($id)
    {
        $loc = Location::with('parent')->find($id);
        if (!$loc) return;

        $this->locationId = $loc->id;
        $this->name = $loc->name;
        $this->parent_location_id = $loc->parent_location_id;
        $this->selectedParentName = $loc->parent ? $loc->parent->name : '';
        $this->isEditMode = true;
        
        $this->resetValidation();
        // Kirim perintah ke browser: Buka Modal Form
        $this->dispatch('open-modal-form');
    }

    // 3. Tombol Hapus Ditekan (Konfirmasi)
    public function confirmDelete($id)
    {
        $loc = Location::find($id);
        if (!$loc) return;

        $this->locationId = $loc->id;
        $this->deleteName = $loc->name;
        
        // Kirim perintah ke browser: Buka Modal Delete
        $this->dispatch('open-modal-delete');
    }

    // 4. Proses Simpan
    public function store()
    {
        $this->validate();

        // Validasi parent ke diri sendiri
        if ($this->isEditMode && $this->locationId == $this->parent_location_id) {
             $this->addError('parent_location_id', 'Lokasi tidak bisa menjadi induk bagi dirinya sendiri.');
             return;
        }

        $data = [
            'name' => $this->name,
            'parent_location_id' => $this->parent_location_id ?: null,
        ];

        if ($this->isEditMode && $this->locationId) {
            Location::findOrFail($this->locationId)->update($data);
            $message = 'Data lokasi berhasil diperbarui.';
        } else {
            Location::create($data);
            $message = 'Data lokasi berhasil ditambahkan.';
        }
        
        session()->flash('message', $message);
        
        $this->resetInputFields();
        $this->dispatch('close-all-modals'); // Tutup semua modal
    }

    // 5. Proses Delete
    public function delete()
    {
        // 1. Cek jika ID tidak ada
        if (!$this->locationId) return;

        try {
            $loc = Location::findOrFail($this->locationId);
            
            // 2. Cek Validasi Child (Business Logic)
            if ($loc->children()->count() > 0) {
                // CASE: GAGAL (Masih ada child)
                // Set flash message error
                session()->flash('error', 'Gagal! Gedung ini masih memiliki ruangan di dalamnya.');
                
                // PENTING: Kita TIDAK melakukan return di sini lagi.
                // Biarkan kode lanjut ke bawah untuk menutup modal.
            } else {
                // CASE: SUKSES (Aman dihapus)
                $loc->delete();
                session()->flash('message', 'Lokasi berhasil dihapus.');
            }

        } catch (\Exception $e) {
            // CASE: ERROR DATABASE (Foreign Key / System Error)
            session()->flash('error', 'Gagal menghapus. Data sedang digunakan atau terkait data lain.');
        }

        // 3. FINAL STEP (Dijalankan baik SUKSES maupun GAGAL)
        // Tutup modal Delete
        $this->dispatch('close-all-modals');
        
        // Bersihkan state (ID dan Nama direset agar tidak nyangkut)
        $this->resetInputFields();
    }

    public function resetInputFields()
    {
        $this->name = '';
        $this->locationId = null;
        $this->parent_location_id = null;
        $this->parentSearch = '';
        $this->selectedParentName = '';
        $this->deleteName = '';
        $this->isEditMode = false;
        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function getParentsProperty()
    {
        return Location::query()
            ->when($this->parentSearch, fn($q) => $q->where('name', 'like', '%' . $this->parentSearch . '%'))
            ->whereNull('parent_location_id')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        $locations = Location::query()
            ->select('locations.*')
            ->with('parent')
            ->leftJoin('locations as parent_table', 'locations.parent_location_id', '=', 'parent_table.id')
            ->when($this->search, function($q) {
                $q->where('locations.name', 'like', '%' . $this->search . '%')
                  ->orWhere('parent_table.name', 'like', '%' . $this->search . '%');
            })
            ->orderByRaw('COALESCE(parent_table.name, locations.name) ASC')
            ->orderByRaw('CASE WHEN locations.parent_location_id IS NULL THEN 0 ELSE 1 END ASC')
            ->orderBy('locations.name', 'asc')
            ->paginate(10);

        return view('livewire.admin.master.location-manager', [
            'locations' => $locations
        ]);
    }
}