<?php

namespace App\Livewire\Admin\Maintenance;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\Maintenance;
use Livewire\Attributes\Url;

/**
 * Class MaintenanceShow
 *
 * Komponen Livewire yang menangani halaman detail (Single Page View) dari sebuah data pemeliharaan.
 * Bertanggung jawab untuk menampilkan informasi lengkap pemeliharaan, termasuk relasi (aset, teknisi, foto),
 * serta mengelola navigasi kontekstual "Kembali" berdasarkan asal akses pengguna.
 *
 * Catatan: Logika presentasi (label & badge warna) telah didelegasikan ke Model Maintenance
 * melalui Accessor (type_label & badge_class).
 *
 * @package App\Livewire\Admin\Maintenance
 */
#[Layout('components.layouts.admin')]
#[Title('Detail Pemeliharaan')]
class MaintenanceShow extends Component
{
    // ==========================================
    // NAVIGATION STATE PROPERTIES
    // ==========================================

    /**
     * Parameter URL untuk mendeteksi asal halaman referer.
     * Contoh: 'asset' jika datang dari halaman detail aset.
     * @var string|null
     */
    #[Url]
    public $from = null;

    /**
     * Parameter URL untuk menyimpan tag aset referensi.
     * Digunakan untuk membangun link kembali ke aset spesifik.
     * @var string|null
     */
    #[Url]
    public $asset_tag = null;

    /**
     * URL tujuan dinamis untuk tombol "Kembali".
     * Ditentukan pada saat runtime (mount).
     * @var string
     */
    public $backUrl;

    // ==========================================
    // DATA PROPERTIES
    // ==========================================

    /**
     * Model data pemeliharaan yang sedang ditampilkan.
     * @var Maintenance
     */
    public Maintenance $maintenance;

    // ==========================================
    // LIFECYCLE HOOKS
    // ==========================================

    /**
     * Lifecycle Method: Mount
     * Dijalankan saat inisialisasi komponen. Melakukan eager loading relasi
     * dan menentukan logika navigasi "Kembali".
     *
     * @param Maintenance $maintenance Model yang di-inject via Route Model Binding.
     */
    public function mount(Maintenance $maintenance)
    {
        // 1. Eager Loading Relasi (Optimasi Query)
        // Memuat detail aset, teknisi yang bertugas, dan foto bukti
        $this->maintenance = $maintenance->load(['asset.model', 'technicians', 'images']);

        // 2. Logika Penentuan URL "Kembali"
        if ($this->from === 'asset' && $this->asset_tag) {
            // Kasus A: User datang dari Halaman Detail Aset
            // Kembali ke Detail Aset > Tab History
            $this->backUrl = route('admin.assets.show', [
                'asset' => $this->asset_tag, 
                'tab'   => 'history'
            ]);
        } else {
            // Kasus B: Akses langsung atau dari Index Maintenance
            // Kembali ke halaman daftar utama
            $this->backUrl = route('admin.maintenances.index');
        }
    }

    // ==========================================
    // RENDER LOGIC
    // ==========================================

    /**
     * Merender tampilan komponen.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.admin.maintenance.maintenance-show');
    }
}