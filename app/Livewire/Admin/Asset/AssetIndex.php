<?php

namespace App\Livewire\Admin\Asset;

use App\Models\Asset;
use App\Models\Category;
use App\Models\AssetStatus;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

/**
 * Class AssetIndex
 *
 * Komponen Livewire yang menangani halaman utama (Index) manajemen aset.
 * Menyediakan antarmuka tabel data dengan fitur:
 * - Pagination dan Filtering (Pencarian, Kategori, Status).
 * - Operasi penghapusan data aman (Soft/Hard delete beserta file fisik).
 * - Fitur cetak/unduh Label QR Code secara massal berdasarkan hasil filter.
 *
 * @package App\Livewire\Admin\Asset
 */
#[Layout('components.layouts.admin')]
#[Title('Kelola Aset')]
class AssetIndex extends Component
{
    use WithPagination;

    // ==========================================
    // FILTER STATE PROPERTIES
    // ==========================================
    
    /** @var string Keyword pencarian global (Tag, Serial, Model, Lokasi) */
    public $search = '';
    
    /** @var string Filter berdasarkan ID Kategori */
    public $category_id = '';
    
    /** @var string Filter berdasarkan ID Status */
    public $status_id = '';

    // ==========================================
    // UI STATE PROPERTIES (MODAL)
    // ==========================================
    
    /** @var bool Kontrol visibilitas modal konfirmasi hapus */
    public $showDeleteModal = false;
    
    /** @var string ID aset yang akan dihapus */
    public $deleteId = '';
    
    /** @var string Nama aset untuk konfirmasi visual di modal */
    public $deleteName = '';

    // ==========================================
    // LIFECYCLE HOOKS
    // ==========================================
    
    /**
     * Hook: Mereset pagination ke halaman 1 saat keyword pencarian berubah.
     */
    public function updatingSearch() { $this->resetPage(); }
    
    /**
     * Hook: Mereset pagination ke halaman 1 saat filter kategori berubah.
     */
    public function updatingCategoryId() { $this->resetPage(); }
    
    /**
     * Hook: Mereset pagination ke halaman 1 saat filter status berubah.
     */
    public function updatingStatusId() { $this->resetPage(); }
    
    // ==========================================
    // ACTION HANDLERS: DELETE
    // ==========================================
    
    /**
     * Memicu tampilan modal konfirmasi hapus.
     *
     * @param int|string $id ID Aset
     */
    public function confirmDelete($id)
    {
        $asset = Asset::find($id);
        if ($asset) {
            $this->deleteId = $asset->id;
            // Format nama: Tag Aset (Nama Aset/Model)
            $this->deleteName = $asset->asset_tag . ' (' . ($asset->name ?? $asset->model->name) . ')'; 
            $this->showDeleteModal = true;
        }
    }

    /**
     * Menutup modal hapus dan membersihkan state terkait.
     */
    public function closeModal()
    {
        $this->showDeleteModal = false;
        $this->reset(['deleteId', 'deleteName']);
    }

    /**
     * Mengeksekusi penghapusan data aset secara permanen.
     * Menangani pembersihan file gambar fisik dari storage.
     */
    public function delete()
    {
        $asset = Asset::find($this->deleteId);
        
        if ($asset) {
            // 1. Cek dan Hapus File Gambar Fisik
            if ($asset->image) {
                Storage::disk('public')->delete($asset->image);
            }
            
            // 2. Hapus Record Database
            $asset->delete();
            
            // 3. Berikan Feedback User
            session()->flash('message', 'Aset berhasil dihapus.');
        }
        
        $this->closeModal();
    }

    // ==========================================
    // DATA QUERY LOGIC
    // ==========================================

    /**
     * Membangun Query Builder Eloquent berdasarkan filter yang aktif.
     * Digunakan secara terpusat untuk tampilan tabel dan fitur export QR.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getFilteredAssetsQuery()
    {
        // 1. Inisialisasi Query dengan Eager Loading (Optimasi N+1)
        return Asset::with(['model.category', 'status', 'defaultLocation.parent']) 
            // 2. Terapkan Filter Pencarian Global
            ->when($this->search, function ($q) {
                $q->where('asset_tag', 'like', '%' . $this->search . '%')
                  ->orWhere('serial', 'like', '%' . $this->search . '%')
                  // Cari berdasarkan Nama Model (Relasi)
                  ->orWhereHas('model', fn($m) => $m->where('name', 'like', '%' . $this->search . '%'))
                  // Cari berdasarkan Lokasi (Hierarki: Ruangan atau Gedung)
                  ->orWhereHas('defaultLocation', function ($l) {
                      $l->where('name', 'like', '%' . $this->search . '%') // Level Child (Ruangan)
                        ->orWhereHas('parent', fn($p) => $p->where('name', 'like', '%' . $this->search . '%')); // Level Parent (Gedung)
                  });
            })
            // 3. Terapkan Filter Spesifik (Kategori & Status)
            ->when($this->category_id, fn($q) => $q->whereHas('model', fn($m) => $m->where('category_id', $this->category_id)))
            ->when($this->status_id, fn($q) => $q->where('asset_status_id', $this->status_id))
            // 4. Urutkan Data (Terbaru di atas)
            ->latest();
    }

    // ==========================================
    // FEATURE: BULK QR DOWNLOAD
    // ==========================================

    /**
     * Menghasilkan file PDF berisi QR Code untuk seluruh aset hasil filter.
     * Melakukan konversi gambar SVG ke Base64 agar kompatibel dengan DomPDF.
     *
     * @param string $size Ukuran label dalam milimeter (default '18')
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadBulkQr($size = '18')
    {
        // 1. Ambil Data (Reuse Logic Filter)
        $assets = $this->getFilteredAssetsQuery()->get();

        if ($assets->isEmpty()) {
            session()->flash('message', 'Tidak ada data aset untuk didownload.');
            return;
        }

        // 2. Konfigurasi Layout PDF (Ukuran Kertas & Font)
        if ($size == '24') {
            $paperSize = [0, 0, 141, 141]; // Point (untuk 50x50mm estimasi)
            $fontSize = '12px';
        } else {
            $paperSize = [0, 0, 100, 100]; // Point (untuk ukuran standar)
            $fontSize = '11px'; 
        }

        // 3. Transformasi Data: Embed QR Code (SVG -> Base64)
        $assets->transform(function ($asset) {
            $filename = 'qr-' . $asset->asset_tag . '.svg';
            $path = public_path('storage/qrcodes/' . $filename);
            
            if (file_exists($path)) {
                $content = file_get_contents($path);
                // Konversi ke format Data URI Base64 agar DomPDF bisa merender SVG
                $asset->qr_base64 = 'data:image/svg+xml;base64,' . base64_encode($content);
            } else {
                $asset->qr_base64 = null; 
            }
            return $asset;
        });

        // 4. Generate PDF View
        $pdf = Pdf::loadView('pdf.asset-labels-bulk', [
            'assets'   => $assets,
            'fontSize' => $fontSize
        ]);

        $pdf->setPaper($paperSize);
        $pdf->setWarnings(false);

        // 5. Stream Download ke Browser
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'QR-BULK-' . $size . '-' . date('dmY') . '.pdf');
    }

    /**
     * Merender tampilan komponen utama.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        //  - Diagram konseptual alur data (Opsional untuk dokumentasi eksternal)
        
        return view('livewire.admin.asset.asset-index', [
            'assets' => $this->getFilteredAssetsQuery()->paginate(10),
            'categories' => Category::orderBy('name')->get(),
            'statuses' => AssetStatus::orderBy('name')->get(),
        ]);
    }
}