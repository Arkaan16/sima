<?php

namespace App\Livewire\Employee\Maintenance;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\MaintenanceForm;
use App\Models\Maintenance;
use App\Models\User;
use Illuminate\Validation\ValidationException;

#[Layout('components.layouts.employee')]
#[Title('Edit Data Pemeliharaan')]
class MaintenanceEdit extends Component
{
    use WithFileUploads;

    public MaintenanceForm $form;

    // UI STATE
    public $assetDisplayString = '';
    public $assetImage = null;
    
    // IMAGE MANAGEMENT
    public $tempPhotos = [];
    public $photosToDelete = [];

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

    /**
     * Inisialisasi data pemeliharaan ke dalam form.
     */
    public function mount(Maintenance $maintenance)
    {
        // Eager load untuk performa kencang
        $maintenance->load(['images', 'technicians', 'asset.model']);
        
        // Isi Form Object
        $this->form->setMaintenance($maintenance);

        // Siapkan info aset (Read-Only)
        $asset = $maintenance->asset;
        if($asset) {
            $this->assetDisplayString = $asset->asset_tag . ' - ' . ($asset->model->name ?? '-') . ' (' . ($asset->serial_number ?? '-') . ')';
            $this->assetImage = $asset->image ?? $asset->model->image ?? null;
        }
    }

    /**
     * Handler Upload Foto Baru (Real-time Validation)
     */
    public function updatedTempPhotos()
    {
        $this->validate([
            'tempPhotos.*' => 'image|max:10240',
        ], [
            'tempPhotos.*.max' => 'Ukuran foto tidak boleh lebih dari 10MB.',
            'tempPhotos.*.image' => 'File harus berupa gambar.',
        ]);

        // Kalkulasi Slot Tersisa: 3 - (Foto DB Aktif) - (Foto Baru di Antrean)
        $activeDbCount = $this->form->maintenance->images->whereNotIn('id', $this->photosToDelete)->count();
        $currentNewCount = count($this->form->photos ?? []);
        $incomingCount = count($this->tempPhotos);

        if (($activeDbCount + $currentNewCount + $incomingCount) > 3) {
            $this->addError('tempPhotos', "Total maksimal 3 foto. Anda memiliki " . ($activeDbCount + $currentNewCount) . " foto aktif.");
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

    public function deleteExistingPhoto($imageId)
    {
        // Tandai ID untuk dihapus saat save()
        if (!in_array($imageId, $this->photosToDelete)) {
            $this->photosToDelete[] = $imageId;
        }
    }

    /**
     * Proses Update Data
     */
    public function save()
    {
        try {
            // Eksekusi update via Form Object
            $this->form->update($this->photosToDelete);
            
            session()->flash('success', 'Data pemeliharaan berhasil diperbarui');
            
            return redirect()->route('employee.maintenances.index');

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
        // Optimasi: Hanya ambil id dan name
        $technicians = User::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('livewire.employee.maintenance.maintenance-edit', [
            'technicians' => $technicians,
        ]);
    }
}