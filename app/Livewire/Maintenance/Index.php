<?php

namespace App\Livewire\Maintenance;

use Livewire\Component;
use App\Models\Maintenance;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

#[Title('Data Pemeliharaan')]
class Index extends Component
{
    use WithPagination;

    // Filter Properties
    public $search = '';
    public $type = '';
    public $date = '';

    // UI & Modal Properties
    public $showDeleteModal = false;
    public $deleteId = null;

    // Lifecycle Hooks
    public function updatedSearch() { $this->resetPage(); }
    public function updatedType() { $this->resetPage(); }
    public function updatedDate() { $this->resetPage(); }

    // --- ACTIONS ---

    public function confirmDelete($id)
    {
        // Cek permission via Gate/Policy sebelum membuka modal
        $maintenance = Maintenance::findOrFail($id);
        Gate::authorize('delete', $maintenance);

        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function closeModal()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    public function delete()
    {
        // 1. Authorize (Pastikan Employee tidak bisa tembus sini)
        $maintenance = Maintenance::findOrFail($this->deleteId);
        Gate::authorize('delete', $maintenance);

        try {
            // 2. Hapus File Fisik
            if (!empty($maintenance->photos) && is_array($maintenance->photos)) {
                foreach ($maintenance->photos as $photo) {
                    if (Storage::disk('public')->exists($photo)) {
                        Storage::disk('public')->delete($photo);
                    }
                }
            }

            // 3. Hapus Data
            $maintenance->delete();
            
            $this->closeModal();
            session()->flash('success', 'Data pemeliharaan berhasil dihapus.');
            
        } catch (\Exception $e) {
            $this->closeModal();
            session()->flash('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    // --- RENDER ---

    public function render()
    {
        $user = Auth::user();
        $isAdmin = $user->role === 'admin';

        // 1. Base Query
        $query = Maintenance::query()
            ->with(['asset', 'technicians']);

        // 2. Filter Role (Logika Pembeda Utama)
        if (!$isAdmin) {
            // Jika Employee: Hanya tampilkan tugas dia sendiri
            $query->whereHas('technicians', function($q) use ($user) {
                $q->where('users.id', $user->id);
            });
        }

        // 3. Filter Global (Search, Type, Date)
        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('asset', fn($qAsset) => $qAsset->where('asset_tag', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('technicians', fn($qTech) => $qTech->where('name', 'like', '%' . $this->search . '%'));
            });
        }

        if ($this->type) $query->where('maintenance_type', $this->type);
        if ($this->date) $query->whereDate('execution_date', $this->date);

        // 4. Get Data
        $maintenances = $query->latest('execution_date')->paginate(10);

        // 5. Transform Data (Khusus Employee: Sort nama dia paling atas di list teknisi)
        if (!$isAdmin) {
            $maintenances->getCollection()->transform(function ($item) use ($user) {
                $sortedTechnicians = $item->technicians->sortBy(function ($tech) use ($user) {
                    return $tech->id === $user->id ? 0 : 1;
                });
                $item->setRelation('technicians', $sortedTechnicians);
                return $item;
            });
        }


        return view('livewire.maintenance.index', [
            'maintenances' => $maintenances,
            'types' => Maintenance::getTypes(),
            'isAdmin' => $isAdmin 
        ]);
    }
}