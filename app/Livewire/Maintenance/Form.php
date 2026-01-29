<?php

namespace App\Livewire\Maintenance;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use App\Livewire\Forms\MaintenanceForm;
use App\Models\Maintenance;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Validation\ValidationException;

#[Title('Data Pemeliharaan')]
class Form extends Component
{
    use WithFileUploads;

    public MaintenanceForm $form;

    // ==========================================
    // STATE PROPERTIES
    // ==========================================
    public bool $isEdit = false;
    public string $pageTitle = 'Tambah Data Pemeliharaan';
    
    // URL Parameters (Khusus Create)
    #[Url(as: 'asset_tag')]
    public $preselectedTag = '';

    #[Url] 
    public $from = ''; 

    public $backUrl; 

    // UI State: Asset Search (Khusus Create)
    public $searchAsset = ''; 
    public $selectedAssetDisplay = null; 
    public $selectedAssetImage = null;    

    // UI State: Asset Display (Khusus Edit)
    public $editAssetDisplayString = '';
    public $editAssetImage = null;

    // Image Management
    public $tempPhotos = [];
    public $photosToDelete = []; // Khusus Edit

    // Static Data
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

    // ==========================================
    // LIFECYCLE: MOUNT
    // ==========================================
    public function mount(Maintenance $maintenance = null)
    {
        if ($maintenance && $maintenance->exists) {
            // --- MODE EDIT ---
            $this->isEdit = true;
            $this->pageTitle = 'Edit Data Pemeliharaan';
            
            // Eager load
            $maintenance->load(['images', 'technicians', 'asset.model']);
            
            // Isi Form Object
            $this->form->setMaintenance($maintenance);

            // Setup Tampilan Aset (Read Only)
            $asset = $maintenance->asset;
            if($asset) {
                $this->editAssetDisplayString = $asset->asset_tag . ' - ' . ($asset->model->name ?? '-');
                $this->editAssetImage = $asset->image ?? $asset->model->image ?? null;
            }

            // Default back URL untuk edit biasanya ke index
            $this->backUrl = route('maintenances.index'); // Sesuaikan route index Anda

        } else {
            // --- MODE CREATE ---
            $this->isEdit = false;
            $this->pageTitle = 'Tambah Data Pemeliharaan';

            // Logika Back URL (seperti kode lama)
            if ($this->from === 'asset' && $this->preselectedTag) {
                $this->backUrl = route('assets.show', [ // Sesuaikan route show aset
                    'asset' => $this->preselectedTag, 
                    'tab' => 'history'
                ]);
            } else {
                $this->backUrl = route('maintenances.index');
            }

            // Logika Preselected Asset dari URL
            if ($this->preselectedTag) {
                $asset = Asset::with('model')->where('asset_tag', $this->preselectedTag)->first();
                if ($asset) {
                    $this->selectAsset($asset->id, $asset->asset_tag . ' - ' . ($asset->model->name ?? 'Unknown Model'), $asset->image ?? $asset->model->image ?? null);
                }
            }
        }
    }

    // ==========================================
    // METHODS: FOTO HANDLING
    // ==========================================
    public function updatedTempPhotos()
    {
        $this->validate([
            'tempPhotos.*' => 'image|max:10240', 
        ], [
            'tempPhotos.*.max' => 'Ukuran foto tidak boleh lebih dari 10MB.',
            'tempPhotos.*.image' => 'File harus berupa gambar.',
        ]);

        // Hitung total foto: (Foto di DB yg belum dihapus) + (Foto baru yg sudah di form) + (Foto yg baru diupload sekarang)
        $activeDbCount = 0;
        if ($this->isEdit && $this->form->maintenance) {
            $activeDbCount = $this->form->maintenance->images->whereNotIn('id', $this->photosToDelete)->count();
        }

        $currentNewCount = count($this->form->photos ?? []);
        $incomingCount = count($this->tempPhotos);
        $totalPotential = $activeDbCount + $currentNewCount + $incomingCount;

        if ($totalPotential > 3) {
            $this->addError('tempPhotos', "Total maksimal 3 foto. " . ($this->isEdit ? "Gabungan foto lama dan baru melebihi batas." : "Anda sudah punya $currentNewCount foto."));
            $this->reset('tempPhotos'); 
            return;
        }

        foreach ($this->tempPhotos as $photo) {
            $this->form->photos[] = $photo;
        }

        $this->reset('tempPhotos');
    }

    public function removeNewPhoto($index)
    {
        $this->form->removePhoto($index);
    }

    // Khusus Edit: Menandai foto lama untuk dihapus
    public function deleteExistingPhoto($imageId)
    {
        if (!in_array($imageId, $this->photosToDelete)) {
            $this->photosToDelete[] = $imageId;
        }
    }

    // ==========================================
    // METHODS: ASSET SELECTION (CREATE ONLY)
    // ==========================================
    public function selectAsset($id, $displayText, $image = null)
    {
        $this->form->asset_id = $id;
        $this->selectedAssetDisplay = $displayText;
        $this->selectedAssetImage = $image;
        $this->reset('searchAsset'); 
    }

    // ==========================================
    // ACTION: SAVE / UPDATE
    // ==========================================
    public function save()
    {
        try {
            if ($this->isEdit) {
                // Update Logic
                $this->form->update($this->photosToDelete);
                session()->flash('success', 'Data pemeliharaan berhasil diperbarui');
                return redirect()->route('maintenances.index'); // Sesuaikan

            } else {
                // Store Logic
                $this->form->store();
                session()->flash('success', 'Data pemeliharaan berhasil ditambahkan');
                return redirect()->to($this->backUrl);
            }

        } catch (ValidationException $e) {
            $this->dispatch('validation-fails');
            throw $e;
        } catch (\Exception $e) {
            $this->dispatch('validation-fails');
            throw $e;
        }
    }

    public function render()
    {
        // Query Assets (Hanya dijalankan saat Mode Create dan User mengetik search)
        $assets = [];
        if (!$this->isEdit) {
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
        }

        $technicians = User::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('livewire.maintenance.form', [
            'assets' => $assets,
            'technicians' => $technicians, 
        ]);
    }
}