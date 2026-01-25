<?php

namespace App\Livewire\Employee\Maintenance;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\Maintenance;
use Livewire\Attributes\Url;

/**
 * Class MaintenanceShow (Employee Version)
 *
 * Versi detail pemeliharaan untuk karyawan.
 * Tampilan sama persis dengan admin, hanya logika navigasi kembali yang disesuaikan
 * agar mengarah ke route employee.
 */
#[Layout('components.layouts.employee')]
#[Title('Detail Pemeliharaan')]
class MaintenanceShow extends Component
{
    // ==========================================
    // NAVIGATION STATE PROPERTIES
    // ==========================================

    #[Url]
    public $from = null;

    #[Url]
    public $asset_tag = null;

    public $backUrl;

    // ==========================================
    // DATA PROPERTIES
    // ==========================================

    public Maintenance $maintenance;

    // ==========================================
    // LIFECYCLE HOOKS
    // ==========================================

    public function mount(Maintenance $maintenance)
    {
        // 1. Eager Loading Relasi
        $this->maintenance = $maintenance->load(['asset.model', 'technicians', 'images']);

        // 2. Logika Navigasi Kembali (Versi Employee)
        if ($this->from === 'asset' && $this->asset_tag) {
            // Kembali ke Detail Aset Employee > Tab History
            $this->backUrl = route('employee.assets.show', [
                'asset' => $this->asset_tag, 
                'tab'   => 'history'
            ]);
        } else {
            // Kembali ke Index Maintenance Employee
            $this->backUrl = route('employee.maintenances.index');
        }
    }

    // ==========================================
    // RENDER LOGIC
    // ==========================================

    public function render()
    {
        return view('livewire.employee.maintenance.maintenance-show');
    }
}