<?php

namespace App\Livewire\Admin\Master;

use App\Models\Location;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Illuminate\Validation\Rule;

#[Layout('components.layouts.admin')]
#[Title('Kelola Lokasi')]
class LocationManager extends Component
{
    use WithPagination;
    protected $paginationTheme = 'tailwind';

    public $locationId;
    public $name;
    public $parent_location_id = null;

    public $parentSearch = '';
    public $selectedParentName = '';

    #[Url(except: '')]
    public $search = '';

    public $showFormModal = false;
    public $showDeleteModal = false;
    public $isEditMode = false;

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

    protected $messages = [
        'name.required' => 'Nama lokasi/ruangan wajib diisi.',
        'name.unique' => 'Nama ruangan ini sudah ada di Gedung tersebut.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    /**
     * LOGIKA PENGAMAN (ANTI BINGUNG):
     * Hanya ambil lokasi yang merupakan ROOT (Induk Utama).
     * Lokasi yang sudah punya parent (alias Ruangan) TIDAK BOLEH jadi parent lagi.
     */
    public function getParentsProperty()
    {
        return Location::query()
            // Filter 1: Cari berdasarkan ketikan user
            ->when($this->parentSearch, fn($q) => $q->where('name', 'like', '%' . $this->parentSearch . '%'))
            // Filter 2: HANYA tampilkan lokasi yang tidak punya induk (Parent ID = NULL)
            // Ini memastikan "Ruang IT" tidak akan muncul di dropdown pilihan.
            ->whereNull('parent_location_id') 
            ->limit(5)
            ->get();
    }

    public function selectParent($id, $name)
    {
        $this->parent_location_id = $id;
        $this->selectedParentName = $name;
        $this->parentSearch = '';
    }

    public function clearParent()
    {
        $this->parent_location_id = null;
        $this->selectedParentName = '';
        $this->parentSearch = '';
    }

    public function render()
    {
        $locations = Location::with('parent')
            ->when($this->search, function($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('parent', fn($s) => $s->where('name', 'like', '%' . $this->search . '%'));
            })
            // Urutkan: Parent (Gedung) dulu, baru Child (Ruangan) biar rapi
            ->orderByRaw('COALESCE(parent_location_id, id), id')
            ->paginate(10);

        return view('livewire.admin.master.location-manager', [
            'locations' => $locations
        ]);
    }

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

    public function create()
    {
        $this->resetInputFields();
        $this->showFormModal = true;
    }

    public function edit($id)
    {
        $this->resetValidation();
        $loc = Location::with('parent')->findOrFail($id);

        $this->locationId = $id;
        $this->name = $loc->name;
        $this->parent_location_id = $loc->parent_location_id;
        $this->selectedParentName = $loc->parent ? $loc->parent->name : '';

        $this->isEditMode = true;
        $this->showFormModal = true;
    }

    public function store()
    {
        $this->validate();

        // Validasi Logika Tambahan: Cegah Edit Data Root menjadi Child dari dirinya sendiri (Hacking prevention)
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
            session()->flash('message', 'Data lokasi berhasil diperbarui.');
        } else {
            Location::create($data);
            session()->flash('message', 'Data lokasi berhasil ditambahkan.');
        }
        $this->closeModal();
    }

    public function confirmDelete($id)
    {
        $this->locationId = $id;
        $this->showDeleteModal = true;
    }

    public function delete()
    {
        if ($this->locationId) {
            try {
                $loc = Location::findOrFail($this->locationId);
                // Cek: Gedung tidak boleh dihapus jika masih ada Ruangan di dalamnya
                if ($loc->children()->count() > 0) {
                    session()->flash('error', 'Gagal! Gedung ini masih memiliki ruangan di dalamnya.');
                } else {
                    $loc->delete();
                    session()->flash('message', 'Lokasi berhasil dihapus.');
                }
            } catch (\Exception $e) {
                session()->flash('error', 'Gagal menghapus. Lokasi sedang digunakan data aset.');
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