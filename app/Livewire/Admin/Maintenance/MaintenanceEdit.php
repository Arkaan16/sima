<?php

namespace App\Livewire\Admin\Maintenance;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\MaintenanceForm;
use App\Models\Maintenance;
use App\Models\User;

/**
 * Class MaintenanceEdit
 *
 * Komponen Livewire yang menangani proses penyuntingan (Edit) data pemeliharaan.
 * Mengelola logika bisnis yang kompleks terkait pembaruan data formulir,
 * manajemen status foto (penambahan foto baru vs penghapusan foto lama),
 * serta validasi integritas jumlah file.
 *
 * @package App\Livewire\Admin\Maintenance
 */
#[Layout('components.layouts.admin')]
#[Title('Edit Data Pemeliharaan')]
class MaintenanceEdit extends Component
{
    use WithFileUploads;

    /**
     * Objek Form Livewire yang menangani validasi inti dan manipulasi data.
     * @var MaintenanceForm
     */
    public MaintenanceForm $form;

    // ==========================================
    // UI STATE PROPERTIES
    // ==========================================

    /** @var string Informasi teks aset untuk tampilan Read-Only */
    public $assetDisplayString = '';
    
    /** @var string|null URL gambar aset untuk tampilan Read-Only */
    public $assetImage = null;

    // ==========================================
    // IMAGE MANAGEMENT STATE
    // ==========================================

    /** @var array Penampung file foto baru yang diunggah sementara */
    public $tempPhotos = [];
    
    /** @var array Daftar ID foto lama yang ditandai untuk dihapus */
    public $photosToDelete = [];

    /**
     * Daftar referensi jenis pemeliharaan untuk dropdown.
     * @var array
     */
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
    // LIFECYCLE HOOKS
    // ==========================================

    /**
     * Lifecycle Method: Mount
     * Dijalankan saat inisialisasi komponen. Mempersiapkan form dengan data existing,
     * melakukan eager loading relasi, dan memformat tampilan info aset.
     *
     * @param Maintenance $maintenance Model yang di-inject via Route Model Binding.
     */
    public function mount(Maintenance $maintenance)
    {
        // 1. Eager Load Relasi yang dibutuhkan
        $maintenance->load(['images', 'technicians', 'asset.model']);
        
        // 2. Isi Form Object dengan data maintenance
        $this->form->setMaintenance($maintenance);

        // 3. Siapkan Tampilan Info Aset (Read-Only)
        $asset = $maintenance->asset;
        if($asset) {
            $this->assetDisplayString = $asset->asset_tag . ' - ' . ($asset->model->name ?? '-') . ' (' . ($asset->serial_number ?? '-') . ')';
            $this->assetImage = $asset->image ?? $asset->model->image ?? null;
        }
    }

    // ==========================================
    // IMAGE HANDLING LOGIC
    // ==========================================

    /**
     * Handler saat file foto baru dipilih (diunggah ke temp).
     * Melakukan validasi real-time dan pengecekan kuota maksimal foto.
     * * Logika Kalkulasi Slot:
     * Total = (Foto DB Aktif - Foto DB Ditandai Hapus) + (Foto Baru Antrean) + (Foto Baru Masuk)
     */
    public function updatedTempPhotos()
    {
        // 1. Validasi awal file upload
        $this->validate([
            'tempPhotos.*' => 'image|max:10240',
        ]);

        // 2. Hitung jumlah foto lama yang masih aktif (tidak dihapus)
        $activeDbPhotos = $this->form->maintenance->images
            ->whereNotIn('id', $this->photosToDelete)
            ->count();
            
        // 3. Hitung foto baru yang sudah ada di antrean
        $currentNewCount = count($this->form->photos ?? []);
        
        // 4. Hitung foto yang baru saja masuk
        $incomingCount = count($this->tempPhotos);
        
        // 5. Kalkulasi Total Akhir
        $total = $activeDbPhotos + $currentNewCount + $incomingCount;

        // 6. Validasi Batas Maksimal (3 Foto)
        if ($total > 3) {
            $this->addError('tempPhotos', "Total maksimal 3 foto. (Tersisa slot untuk " . (3 - ($activeDbPhotos + $currentNewCount)) . " foto lagi).");
            $this->reset('tempPhotos'); 
            return;
        }

        // 7. Pindahkan file valid ke properti Form
        foreach ($this->tempPhotos as $photo) {
            $this->form->photos[] = $photo;
        }

        // 8. Bersihkan temp array
        $this->reset('tempPhotos');
    }

    /**
     * Membatalkan/menghapus foto baru dari daftar antrean upload.
     *
     * @param int $index Indeks array foto baru.
     */
    public function removeNewPhoto($index)
    {
        $this->form->removePhoto($index);
    }

    /**
     * Menandai foto lama (dari database) untuk dihapus nanti saat disimpan.
     * Foto belum dihapus secara fisik pada tahap ini.
     *
     * @param int $imageId ID record maintenance_images.
     */
    public function deleteExistingPhoto($imageId)
    {
        $this->photosToDelete[] = $imageId;
    }

    // ==========================================
    // PERSISTENCE LOGIC
    // ==========================================

    /**
     * Mengeksekusi penyimpanan perubahan data ke database.
     * Menangani proses update via Form Object dan manajemen error handling.
     *
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function save()
    {
        try {
            // 1. Delegasikan update ke Form Object (kirim daftar foto yg dihapus)
            $this->form->update($this->photosToDelete);
            
            // 2. Reset state hapus foto jika sukses
            $this->photosToDelete = [];
            
            // 3. Berikan feedback dan redirect
            session()->flash('success', 'Data pemeliharaan berhasil diperbarui');
            return redirect()->route('admin.maintenances.index');
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            // 4a. Tangani Error Validasi Form
            foreach ($e->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }
            // Kirim sinyal ke UI untuk stop loading spinner
            $this->dispatch('reset-loading');
            return;
            
        } catch (\Exception $e) {
            // 4b. Tangani Error Sistem Umum
            $this->addError('form', 'Terjadi kesalahan: ' . $e->getMessage());
            // Kirim sinyal ke UI untuk stop loading spinner
            $this->dispatch('reset-loading');
            return;
        }
    }

    // ==========================================
    // RENDER LOGIC
    // ==========================================

    /**
     * Merender tampilan komponen.
     * Memuat data referensi teknisi untuk dropdown.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $technicians = User::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.maintenance.maintenance-edit', [
            'technicians' => $technicians,
        ]);
    }
}