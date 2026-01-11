<?php

namespace App\Livewire\Admin\Asset;

use App\Models\Asset;
use App\Models\Category;
use App\Models\AssetStatus;
// use App\Models\Location; // Tidak perlu dipanggil lagi karena dropdown dihapus
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

#[Layout('components.layouts.admin')]
#[Title('Kelola Aset')]
class AssetIndex extends Component
{
    use WithPagination;

    // ==========================================
    // FILTER PROPERTIES
    // ==========================================
    public $search = '';
    public $category_id = '';
    public $status_id = '';
    // public $location_id = ''; // DIHAPUS sesuai permintaan

    // ==========================================
    // MODAL DELETE PROPERTIES
    // ==========================================
    public $showDeleteModal = false;
    public $deleteId = '';
    public $deleteName = '';

    // ==========================================
    // LIFECYCLE HOOKS
    // ==========================================
    public function updatingSearch() { $this->resetPage(); }
    public function updatingCategoryId() { $this->resetPage(); }
    public function updatingStatusId() { $this->resetPage(); }
    
    // ==========================================
    // DELETE ACTIONS
    // ==========================================
    public function confirmDelete($id)
    {
        $asset = Asset::find($id);
        if ($asset) {
            $this->deleteId = $asset->id;
            $this->deleteName = $asset->asset_tag . ' (' . ($asset->name ?? $asset->model->name) . ')'; 
            $this->showDeleteModal = true;
        }
    }

    public function closeModal()
    {
        $this->showDeleteModal = false;
        $this->reset(['deleteId', 'deleteName']);
    }

    public function delete()
    {
        $asset = Asset::find($this->deleteId);
        if ($asset) {
            if ($asset->image) {
                Storage::disk('public')->delete($asset->image);
            }
            $asset->delete();
            session()->flash('message', 'Aset berhasil dihapus.');
        }
        $this->closeModal();
    }

    // ==========================================
    // QUERY LOGIC (DIPERBARUI)
    // ==========================================
    private function getFilteredAssetsQuery()
    {
        // Memuat relasi parent pada defaultLocation
        return Asset::with(['model.category', 'status', 'defaultLocation.parent']) 
            ->when($this->search, function ($q) {
                $q->where('asset_tag', 'like', '%' . $this->search . '%')
                  ->orWhere('serial', 'like', '%' . $this->search . '%')
                  // Cari Nama Model
                  ->orWhereHas('model', fn($m) => $m->where('name', 'like', '%' . $this->search . '%'))
                  
                  // Cari Nama Lokasi (Child) ATAU Nama Gedung (Parent)
                  ->orWhereHas('defaultLocation', function ($l) {
                      $l->where('name', 'like', '%' . $this->search . '%') // Cari Ruangan
                        ->orWhereHas('parent', fn($p) => $p->where('name', 'like', '%' . $this->search . '%')); // Cari Gedung
                  });
            })
            ->when($this->category_id, fn($q) => $q->whereHas('model', fn($m) => $m->where('category_id', $this->category_id)))
            ->when($this->status_id, fn($q) => $q->where('asset_status_id', $this->status_id))
            ->latest();
    }

    // ==========================================
    // DOWNLOAD QR (SUDAH DIKEMBALIKAN UTUH)
    // ==========================================
    public function downloadBulkQr($size = '18')
    {
        $assets = $this->getFilteredAssetsQuery()->get();

        if ($assets->isEmpty()) {
            session()->flash('message', 'Tidak ada data aset untuk didownload.');
            return;
        }

        if ($size == '24') {
            $paperSize = [0, 0, 141, 141]; 
            $fontSize = '12px';
        } else {
            $paperSize = [0, 0, 100, 100]; 
            $fontSize = '11px'; 
        }

        $assets->transform(function ($asset) {
            $filename = 'qr-' . $asset->asset_tag . '.svg';
            $path = public_path('storage/qrcodes/' . $filename);
            
            if (file_exists($path)) {
                $content = file_get_contents($path);
                $asset->qr_base64 = 'data:image/svg+xml;base64,' . base64_encode($content);
            } else {
                $asset->qr_base64 = null; 
            }
            return $asset;
        });

        $pdf = Pdf::loadView('pdf.asset-labels-bulk', [
            'assets'   => $assets,
            'fontSize' => $fontSize
        ]);

        $pdf->setPaper($paperSize);
        $pdf->setWarnings(false);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'QR-BULK-' . $size . '-' . date('dmY') . '.pdf');
    }

    public function render()
    {
        return view('livewire.admin.asset.asset-index', [
            'assets' => $this->getFilteredAssetsQuery()->paginate(10),
            'categories' => Category::orderBy('name')->get(),
            'statuses' => AssetStatus::orderBy('name')->get(),
            // Location tidak dikirim lagi karena dropdown dihapus
        ]);
    }
}