<?php

namespace App\Livewire\Admin\Maintenance;

use Livewire\Component;
use App\Models\Maintenance;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

/**
 * Class MaintenanceIndex
 *
 * Komponen Livewire untuk menampilkan dan mengelola daftar riwayat pemeliharaan.
 * Menyediakan antarmuka tabel dengan fitur filtering komprehensif (Search, Tipe, Tanggal),
 * paginasi, serta manajemen penghapusan data secara aman.
 *
 * Catatan: Logika visual (Badge & Label) didelegasikan ke Model Maintenance.
 *
 * @package App\Livewire\Admin\Maintenance
 */
#[Layout('components.layouts.admin')]
#[Title('Data Pemeliharaan')]
class MaintenanceIndex extends Component
{
    use WithPagination;

    // ==========================================
    // FILTER STATE PROPERTIES
    // ==========================================
    
    /** @var string Keyword pencarian global (Judul, Aset, Teknisi, Deskripsi) */
    public $search = '';

    /** @var string Filter berdasarkan jenis pemeliharaan */
    public $type = '';

    /** @var string Filter berdasarkan tanggal eksekusi */
    public $date = '';

    // ==========================================
    // UI & MODAL STATE PROPERTIES
    // ==========================================

    /** @var bool Kontrol visibilitas modal konfirmasi hapus */
    public $showDeleteModal = false;

    /** @var int|string|null ID data yang akan dihapus */
    public $deleteId = null;

    /** @var string Judul statis halaman */
    public $pageTitle = 'Rekaman Pemeliharaan';

    // ==========================================
    // LIFECYCLE HOOKS
    // ==========================================

    /**
     * Hook: Mereset pagination ke halaman 1 saat keyword pencarian berubah.
     */
    public function updatedSearch() { $this->resetPage(); }
    
    /**
     * Hook: Mereset pagination ke halaman 1 saat filter tipe berubah.
     */
    public function updatedType() { $this->resetPage(); }
    
    /**
     * Hook: Mereset pagination ke halaman 1 saat filter tanggal berubah.
     */
    public function updatedDate() { $this->resetPage(); }

    // ==========================================
    // ACTION HANDLERS
    // ==========================================

    /**
     * Memicu tampilan modal konfirmasi hapus untuk ID tertentu.
     *
     * @param int|string $id ID Maintenance
     */
    public function confirmDelete($id)
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    /**
     * Menutup modal konfirmasi dan membersihkan state ID.
     */
    public function closeModal()
    {
        $this->showDeleteModal = false;
        $this->deleteId = null;
    }

    /**
     * Mengeksekusi penghapusan data pemeliharaan beserta file terkait.
     */
    public function delete()
    {
        try {
            // 1. Cari data berdasarkan ID
            $maintenance = Maintenance::findOrFail($this->deleteId);

            // 2. Bersihkan File Fisik (Foto)
            if (!empty($maintenance->photos) && is_array($maintenance->photos)) {
                foreach ($maintenance->photos as $photo) {
                    if (Storage::disk('public')->exists($photo)) {
                        Storage::disk('public')->delete($photo);
                    }
                }
            }

            // 3. Hapus Record Database
            $maintenance->delete();
            
            // 4. Tutup Modal & Berikan Feedback
            $this->closeModal();
            session()->flash('success', 'Data pemeliharaan dan foto berhasil dihapus.');
            
        } catch (\Exception $e) {
            // 5. Tangani Error
            $this->closeModal();
            session()->flash('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    // ==========================================
    // RENDER LOGIC
    // ==========================================

    /**
     * Merender tampilan komponen dengan data yang sudah difilter.
     * Menggunakan eager loading dan query builder dinamis untuk performa optimal.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // 1. Inisialisasi Query dengan Eager Loading
        $query = Maintenance::query()
            ->with(['asset', 'technicians']);

        // 2. Terapkan Filter Pencarian Global
        if ($this->search) {
            $query->where(function (Builder $q) {
                // Cari di kolom lokal
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%')
                  // Cari di relasi Aset (Tag)
                  ->orWhereHas('asset', function (Builder $qAsset) {
                      $qAsset->where('asset_tag', 'like', '%' . $this->search . '%');
                  })
                  // Cari di relasi Teknisi (Nama)
                  ->orWhereHas('technicians', function (Builder $qTech) {
                      $qTech->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        // 3. Terapkan Filter Tipe Pemeliharaan
        if ($this->type) {
            $query->where('maintenance_type', $this->type);
        }

        // 4. Terapkan Filter Tanggal Eksekusi
        if ($this->date) {
            $query->whereDate('execution_date', $this->date);
        }

        // 5. Eksekusi Query dengan Paginasi & Sorting
        $maintenances = $query->latest('execution_date')->paginate(10);

        // 6. Ambil Data Referensi Tipe (dari Model)
        $types = Maintenance::getTypes();

        return view('livewire.admin.maintenance.maintenance-index', [
            'maintenances' => $maintenances,
            'types' => $types
        ]);
    }
}