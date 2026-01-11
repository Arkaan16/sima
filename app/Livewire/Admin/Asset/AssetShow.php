<?php

namespace App\Livewire\Admin\Asset;

use Livewire\Component;
use App\Models\Asset;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Barryvdh\DomPDF\Facade\Pdf;

#[Layout('components.layouts.admin')]
#[Title('Detail Aset')]
class AssetShow extends Component
{
    public Asset $asset;

    public function mount(Asset $asset)
    {
        $this->asset = $asset;
    }

    /**
     * Fungsi Download QR Code
     * @param string $size '24' atau '18'
     */
    public function downloadQr($size)
    {
        // 1. Konfigurasi Ukuran & Font
        if ($size == '24') {
            // Label Sedang
            $paperSize = [0, 0, 141, 141]; // ~50mm
            $fontSize = '12px'; // Font lebih besar
        } else {
            // Label Kecil (Ukuran 18 yg bermasalah)
            $paperSize = [0, 0, 100, 100]; // ~35mm
            $fontSize = '9px'; // Font HARUS kecil agar muat
        }

        // 2. Load File (Sama seperti sebelumnya)
        $filename = 'qr-' . $this->asset->asset_tag . '.svg';
        $path = public_path('storage/qrcodes/' . $filename);

        if (!file_exists($path)) {
            abort(404, 'QR Code belum digenerate.');
        }

        $qrContent = file_get_contents($path);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrContent);

        // 3. Generate PDF
        $pdf = Pdf::loadView('pdf.asset-label', [
            'asset'     => $this->asset,
            'qrImage'   => $qrBase64,
            'fontSize'  => $fontSize
        ]);

        $pdf->setPaper($paperSize);
        $pdf->setWarnings(false);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'LBL-' . $this->asset->asset_tag . '.pdf');
    }

    public function render()
    {
        return view('livewire.admin.asset.asset-show');
    }
}