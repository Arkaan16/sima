<?php

namespace App\Livewire\Admin\Asset;

use Livewire\Component;
use App\Models\Asset;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Url; 
use Livewire\WithPagination;

/**
 * Class AssetShow
 *
 * Komponen Livewire yang menangani halaman detail (Single Page View) dari sebuah aset.
 * Bertanggung jawab untuk:
 * - Menampilkan informasi atribut aset.
 * - Menyediakan navigasi tab (Detail & Riwayat).
 * - Menampilkan riwayat pemeliharaan dengan paginasi.
 * - Meng-generate dan mengunduh label QR Code fisik dalam format PDF.
 *
 * Catatan: Logika presentasi (label & badge warna maintenance) didelegasikan ke Model Maintenance.
 *
 * @package App\Livewire\Admin\Asset
 */
#[Layout('components.layouts.admin')]
#[Title('Detail Aset')]
class AssetShow extends Component
{
    use WithPagination;

    /**
     * Model aset utama yang sedang ditampilkan.
     * @var Asset
     */
    public Asset $asset;

    /**
     * Menyimpan state tab yang sedang aktif.
     * Disinkronkan dengan URL query string '?tab=...' agar state bertahan saat refresh.
     * @var string
     */
    #[Url(as: 'tab')] 
    public $activeTab = 'detail';

    // =================================================================
    // LIFECYCLE HOOKS
    // =================================================================

    /**
     * Lifecycle Method: Mount
     * Dijalankan saat komponen diinisialisasi.
     *
     * @param Asset $asset Instance model yang di-inject via Route Model Binding.
     */
    public function mount(Asset $asset)
    {
        $this->asset = $asset;
    }

    // =================================================================
    // ACTION HANDLERS
    // =================================================================

    /**
     * Menghasilkan file PDF berisi label QR Code untuk aset ini.
     * Menggunakan DomPDF dengan penanganan khusus untuk gambar SVG.
     *
     * @param string $size Ukuran label ('24' untuk besar, lainnya untuk kecil).
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadQr($size)
    {
        // 1. Konfigurasi Dimensi Kertas & Font
        if ($size == '24') {
            $paperSize = [0, 0, 141, 141]; // Point (~50mm)
            $fontSize = '12px';
        } else {
            $paperSize = [0, 0, 100, 100]; // Point (~35mm)
            $fontSize = '9px';
        }

        // 2. Validasi Keberadaan File Fisik QR Code
        $filename = 'qr-' . $this->asset->asset_tag . '.svg';
        $path = public_path('storage/qrcodes/' . $filename);

        if (!file_exists($path)) {
            abort(404, 'QR Code belum digenerate.');
        }

        // 3. Konversi SVG ke Base64 (Wajib untuk embedding di DomPDF)
        $qrContent = file_get_contents($path);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrContent);

        // 4. Generate PDF Object
        $pdf = Pdf::loadView('pdf.asset-label', [
            'asset'     => $this->asset,
            'qrImage'   => $qrBase64,
            'fontSize'  => $fontSize
        ]);

        $pdf->setPaper($paperSize);
        $pdf->setWarnings(false);

        // 5. Stream Download ke Browser
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'LBL-' . $this->asset->asset_tag . '.pdf');
    }

    /**
     * Mengubah tab yang aktif saat ini.
     *
     * @param string $tab Identifier tab (misal: 'detail' atau 'maintenance')
     */
    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // =================================================================
    // RENDER LOGIC
    // =================================================================

    /**
     * Merender tampilan komponen.
     * Mengambil data maintenance terkait dengan pengurutan tanggal terbaru.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // 1. Query Data Maintenance (Relasi HasMany)
        $maintenances = $this->asset->maintenances()
            ->with(['technicians']) // Eager Load
            ->orderBy('execution_date', 'desc')
            ->paginate(10);

        // 2. Return View
        return view('livewire.admin.asset.asset-show', [
            'maintenances' => $maintenances
        ]);
    }
}