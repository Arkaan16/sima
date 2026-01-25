<?php

namespace App\Livewire\Employee\Maintenance;

use Livewire\Component;
use App\Models\Maintenance;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class MaintenanceIndex (Employee Version)
 *
 * Versi khusus karyawan.
 * Fitur:
 * - Menampilkan daftar maintenance milik user yang login.
 * - Teknisi yang login selalu tampil paling atas di kolom teknisi.
 * - HANYA BISA: Lihat (Index/Show) dan Edit.
 * - TIDAK BISA: Hapus.
 */
#[Layout('components.layouts.employee')]
#[Title('Pekerjaan Pemeliharaan Saya')]
class MaintenanceIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $type = '';
    public $date = '';

    // Lifecycle Hooks
    public function updatedSearch() { $this->resetPage(); }
    public function updatedType() { $this->resetPage(); }
    public function updatedDate() { $this->resetPage(); }

    public function render()
    {
        $userId = Auth::id();

        // 1. Inisialisasi Query (Filter: Hanya tugas user login)
        $query = Maintenance::query()
            ->with(['asset', 'technicians'])
            ->whereHas('technicians', function($q) use ($userId) {
                $q->where('users.id', $userId);
            });

        // 2. Filter Pencarian Global
        if ($this->search) {
            $query->where(function (Builder $q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  ->orWhereHas('asset', fn($qAsset) => $qAsset->where('asset_tag', 'like', '%' . $this->search . '%'))
                  ->orWhereHas('technicians', fn($qTech) => $qTech->where('name', 'like', '%' . $this->search . '%'));
            });
        }

        // 3. Filter Tipe
        if ($this->type) {
            $query->where('maintenance_type', $this->type);
        }

        // 4. Filter Tanggal
        if ($this->date) {
            $query->whereDate('execution_date', $this->date);
        }

        // 5. Eksekusi Query
        $maintenances = $query->latest('execution_date')->paginate(10);

        // 6. Sorting Manual: User login ditaruh di paling atas list teknisi
        $maintenances->getCollection()->transform(function ($item) use ($userId) {
            $sortedTechnicians = $item->technicians->sortBy(function ($tech) use ($userId) {
                return $tech->id === $userId ? 0 : 1;
            });
            $item->setRelation('technicians', $sortedTechnicians);
            return $item;
        });

        $types = Maintenance::getTypes();

        return view('livewire.employee.maintenance.maintenance-index', [
            'maintenances' => $maintenances,
            'types' => $types
        ]);
    }
}