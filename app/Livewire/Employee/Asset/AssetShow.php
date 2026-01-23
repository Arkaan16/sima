<?php

namespace App\Livewire\Employee\Asset;

use Livewire\Component;
use App\Models\Asset;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Url; 
use Livewire\WithPagination;

/**
 * Class AssetShow (Employee Version)
 *
 * Versi Read-Only untuk karyawan.
 * Fitur sama persis dengan admin (Tabs, History, QR Download),
 * Kecuali fungsi edit/update data aset.
 */
#[Layout('components.layouts.employee')]
#[Title('Detail Aset')]
class AssetShow extends Component
{
    use WithPagination;

    public Asset $asset;

    #[Url(as: 'tab')] 
    public $activeTab = 'detail';

    public function mount(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function downloadQr($size)
    {
        if ($size == '24') {
            $paperSize = [0, 0, 141, 141]; 
            $fontSize = '12px';
        } else {
            $paperSize = [0, 0, 100, 100]; 
            $fontSize = '9px';
        }

        $filename = 'qr-' . $this->asset->asset_tag . '.svg';
        $path = public_path('storage/qrcodes/' . $filename);

        if (!file_exists($path)) {
            // Opsional: Bisa diganti session flash jika tidak ingin error page
            abort(404, 'QR Code belum digenerate oleh Admin.');
        }

        $qrContent = file_get_contents($path);
        $qrBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrContent);

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

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function render()
    {
        // Query Maintenance History
        $maintenances = $this->asset->maintenances()
            ->with(['technicians']) 
            ->orderBy('execution_date', 'desc')
            ->paginate(10);

        return view('livewire.employee.asset.asset-show', [
            'maintenances' => $maintenances
        ]);
    }
}