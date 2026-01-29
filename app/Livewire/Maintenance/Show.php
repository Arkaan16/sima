<?php

namespace App\Livewire\Maintenance;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Maintenance;
use Livewire\Attributes\Url;

#[Title('Detail Pemeliharaan')]
class Show extends Component
{
    // ==========================================
    // STATE PROPERTIES
    // ==========================================

    #[Url]
    public $from = null;

    #[Url]
    public $asset_tag = null;

    public $backUrl;

    public Maintenance $maintenance;

    // ==========================================
    // LIFECYCLE
    // ==========================================

    public function mount(Maintenance $maintenance)
    {
        // 1. Eager Loading Relasi untuk performa
        $this->maintenance = $maintenance->load(['asset.model', 'technicians', 'images']);

        // 2. Logika Navigasi Kembali (Generic Route)
        // Kita menggunakan route name umum, tanpa prefix 'admin' atau 'employee'
        if ($this->from === 'asset' && $this->asset_tag) {
            // Kembali ke Detail Aset > Tab History
            $this->backUrl = route('assets.show', [
                'asset' => $this->asset_tag, 
                'tab'   => 'history'
            ]);
        } else {
            // Kembali ke Index Maintenance
            $this->backUrl = route('maintenances.index');
        }
    }

    public function render()
    {
        return view('livewire.maintenance.show');
    }
}