<?php

namespace App\Livewire\Employee\Maintenance;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\MaintenanceForm;
use App\Models\Asset;
use App\Models\User;
use Livewire\Attributes\Url;
use Illuminate\Validation\ValidationException; // Tambahkan import ini

#[Layout('components.layouts.employee')]
#[Title('Tambah Data Pemeliharaan')]
class MaintenanceCreate extends Component
{
    use WithFileUploads;

    public MaintenanceForm $form;

    // UI STATE
    public $searchAsset = ''; 
    public $selectedAssetDisplay = null; 
    public $selectedAssetImage = null;    

    // --- BAGIAN URL & BACK URL ---
    #[Url(as: 'asset_tag')]
    public $preselectedTag = '';

    #[Url] // 1. Tangkap parameter 'from'
    public $from = ''; 

    public $backUrl; // 2. Variabel untuk menyimpan URL tujuan kembali

    public $tempPhotos = [];

    public $maintenanceTypes = [
        'Preventive' => 'Pencegahan',
        'Corrective' => 'Perbaikan',
        'Calibration' => 'Kalibrasi',
        'Predictive' => 'Prediksi',
        'Routine Inspection' => 'Inspeksi Rutin',
        'Emergency Repair' => 'Perbaikan Darurat',
        'Parts Replacement' => 'Penggantian Suku Cadang',
        'Software Update' => 'Pembaruan Perangkat Lunak',
        'Cleaning' => 'Pembersihan',
    ];

    public function mount()
    {
        // 3. Logika Penentuan URL Kembali
        if ($this->from === 'asset' && $this->preselectedTag) {
            // Jika datang dari aset, kembali ke tab history aset tersebut
            $this->backUrl = route('employee.assets.show', [
                'asset' => $this->preselectedTag, 
                'tab' => 'history'
            ]);
        } else {
            // Default: Kembali ke index maintenance
            $this->backUrl = route('employee.maintenances.index');
        }

        // Logika Preselected Asset (Existing)
        if ($this->preselectedTag) {
            $asset = Asset::with('model')->where('asset_tag', $this->preselectedTag)->first();

            if ($asset) {
                $this->form->asset_id = $asset->id;
                $this->selectedAssetDisplay = $asset->asset_tag . ' - ' . ($asset->model->name ?? 'Unknown Model');
                $this->selectedAssetImage = $asset->image ?? $asset->model->image ?? null; 
            }
        }
    }

    public function updatedTempPhotos()
    {
        $this->validate([
            'tempPhotos.*' => 'image|max:10240', 
        ], [
            'tempPhotos.*.max' => 'Ukuran foto tidak boleh lebih dari 10MB.',
            'tempPhotos.*.image' => 'File harus berupa gambar.',
        ]);

        $currentCount = count($this->form->photos ?? []);
        $incomingCount = count($this->tempPhotos);

        if (($currentCount + $incomingCount) > 3) {
            $this->addError('tempPhotos', "Maksimal hanya 3 foto. Anda sudah punya $currentCount foto.");
            $this->reset('tempPhotos'); 
            return;
        }

        foreach ($this->tempPhotos as $photo) {
            $this->form->photos[] = $photo;
        }

        $this->reset('tempPhotos');
    }

    public function removePhoto($index)
    {
        $this->form->removePhoto($index);
    }

    public function selectAsset($id, $displayText, $image = null)
    {
        $this->form->asset_id = $id;
        $this->selectedAssetDisplay = $displayText;
        $this->selectedAssetImage = $image;
        $this->reset('searchAsset'); 
    }

    public function save()
    {
        try {
            // Coba simpan
            $this->form->store();
            
            session()->flash('success', 'Data pemeliharaan berhasil ditambahkan');
            
            // Redirect.
            // Saat ini terjadi, AlpineJS di frontend MASIH loading (bagus, mencegah double click)
            // sampai halaman benar-benar berpindah.
            return redirect()->to($this->backUrl);

        } catch (ValidationException $e) {
            // JIKA ERROR VALIDASI:
            // Kirim event ke browser: "Hei, ada error nih, matikan loadingnya!"
            $this->dispatch('validation-fails');
            
            // Lempar ulang errornya agar pesan merah muncul di form
            throw $e;
        } catch (\Exception $e) {
            // Error lain (misal database mati), juga matikan loading
            $this->dispatch('validation-fails');
            throw $e;
        }
    }

    public function render()
    {
        $assets = Asset::query()
            ->select('id', 'asset_tag', 'asset_model_id', 'image') 
            ->with('model:id,name,image') 
            ->when($this->searchAsset, function($query) {
                $query->where(function($q) {
                    $q->where('asset_tag', 'like', '%'.$this->searchAsset.'%')
                    ->orWhereHas('model', function($sq) {
                        $sq->where('name', 'like', '%'.$this->searchAsset.'%');
                    });
                });
            })
            ->orderBy('asset_tag')
            ->take(10) 
            ->get();

        $technicians = User::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('livewire.employee.maintenance.maintenance-create', [
            'assets' => $assets,
            'technicians' => $technicians, 
        ]);
    }
}