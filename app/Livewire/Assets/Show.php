<?php

namespace App\Livewire\Assets;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Asset;
use Barryvdh\DomPDF\Facade\Pdf; // <--- PENTING: Tambahkan ini

#[Title('Detail Aset')]
class Show extends Component
{
    public Asset $asset;

    public function mount(Asset $asset)
    {
        $this->authorize('view', $asset);
        
        $this->asset = $asset->load([
            'model.category', 
            'model.manufacturer',
            'status', 
            'defaultLocation.parent',
            'supplier',
            'assignedTo',
            // PERBAIKAN DISINI: Tambahkan orderByDesc pada relasi maintenances
            'maintenances' => function ($query) {
                $query->with('technicians') // Tetap load teknisi
                      ->orderByDesc('execution_date') // Urutkan tanggal pelaksanaan terbaru
                      ->orderByDesc('created_at');    // Jika tanggal sama, urutkan inputan terbaru
            }
        ]);
    }

    /**
     * Fungsi untuk mencetak Label QR Code Single Aset
     */
    public function downloadQr($size)
    {
        // 1. Tentukan Ukuran Kertas & Font
        if ($size == '24') {
            $paperSize = [0, 0, 141, 141]; // Besar (5cm)
            $fontSize = '12px';
        } else {
            $paperSize = [0, 0, 100, 100]; // Kecil (3.5cm)
            $fontSize = '9px';
        }

        // 2. Ambil File Gambar QR dari Storage
        $filename = 'qr-' . $this->asset->asset_tag . '.svg';
        $path = public_path('storage/qrcodes/' . $filename);

        // 3. Validasi Keberadaan File
        if (!file_exists($path)) {
            // Kirim notifikasi error ke user (bukan abort 404 halaman putih)
            $this->dispatch('notify', 'QR Code belum digenerate oleh Admin.'); // Opsional jika pakai toaster
            session()->flash('error', 'QR Code fisik belum digenerate. Hubungi Admin.');
            return;
        }

        // 4. Konversi SVG ke Base64 (Agar terbaca oleh DomPDF)
        $qrContent = file_get_contents($path);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrContent);

        // 5. Generate PDF
        $pdf = Pdf::loadView('exports.asset-label', [
            'asset'     => $this->asset,
            'qrImage'   => $qrBase64,
            'fontSize'  => $fontSize
        ]);

        $pdf->setPaper($paperSize);
        $pdf->setWarnings(false);

        // 6. Stream Download
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'LBL-' . $this->asset->asset_tag . '.pdf');
    }

    public function render()
    {
        return view('livewire.assets.show');
    }
}