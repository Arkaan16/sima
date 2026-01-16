<?php

namespace App\Livewire\Admin\Maintenance;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Livewire\Forms\MaintenanceForm;
use App\Models\Asset;
use App\Models\User;
use Livewire\Attributes\Url;

/**
 * Class MaintenanceCreate
 *
 * Komponen Livewire yang menangani halaman pembuatan data pemeliharaan (maintenance).
 * Bertanggung jawab untuk mengontrol antarmuka input, menangani unggahan file sementara,
 * serta mengatur logika pencarian aset secara real-time sebelum data diserahkan ke Form Object.
 *
 * @package App\Livewire\Admin\Maintenance
 */
#[Layout('components.layouts.admin')]
#[Title('Tambah Data Pemeliharaan')]
class MaintenanceCreate extends Component
{
    use WithFileUploads;

    /**
     * Objek Form Livewire yang menangani validasi inti dan penyimpanan data.
     * @var MaintenanceForm
     */
    public MaintenanceForm $form;

    // ==========================================
    // UI STATE PROPERTIES
    // ==========================================

    /** @var string Keyword pencarian untuk dropdown aset */
    public $searchAsset = ''; 
    
    /** @var string|null Label aset yang dipilih untuk ditampilkan di UI */
    public $selectedAssetDisplay = null; 
    
    /** @var string|null URL gambar aset yang dipilih */
    public $selectedAssetImage = null;   

    /**
     * Parameter URL untuk menangkap 'asset_tag'.
     * Digunakan untuk otomatis memilih aset jika halaman dibuka via Scan QR.
     * @var string
     */
    #[Url(as: 'asset_tag')]
    public $preselectedTag = '';

    /**
     * Array sementara untuk menampung file foto yang baru diunggah.
     * File akan dipindahkan ke properti Form setelah lolos validasi.
     * @var array
     */
    public $tempPhotos = [];

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
     * Dijalankan saat inisialisasi komponen. Menangani logika pre-seleksi aset
     * jika parameter tag tersedia di URL.
     */
    public function mount()
    {
        // 1. Cek parameter tag dari URL (Skenario Scan QR)
        if ($this->preselectedTag) {
            
            // 2. Query data aset terkait
            $asset = Asset::with('model')->where('asset_tag', $this->preselectedTag)->first();

            // 3. Isi state form dan UI jika aset ditemukan
            if ($asset) {
                $this->form->asset_id = $asset->id;
                $this->selectedAssetDisplay = $asset->asset_tag . ' - ' . ($asset->model->name ?? 'Unknown Model');
                $this->selectedAssetImage = $asset->image ?? $asset->model->image ?? null; 
            }
        }
    }

    // ==========================================
    // FILE UPLOAD HANDLERS
    // ==========================================

    /**
     * Hook yang berjalan otomatis saat properti $tempPhotos diperbarui (file dipilih).
     * Melakukan validasi real-time dan membatasi jumlah total foto.
     */
    public function updatedTempPhotos()
    {
        // 1. Validasi awal tipe dan ukuran file
        $this->validate([
            'tempPhotos.*' => 'image|max:2048',
        ]);

        // 2. Hitung total foto (Existing di Form + Baru di Temp)
        $currentCount = count($this->form->photos ?? []);
        $incomingCount = count($this->tempPhotos);

        // 3. Validasi batas maksimal (Max 3 foto)
        if (($currentCount + $incomingCount) > 3) {
            $this->addError('tempPhotos', "Maksimal hanya 3 foto. Anda sudah punya $currentCount foto.");
            $this->reset('tempPhotos'); 
            return;
        }

        // 4. Pindahkan file dari temp ke properti Form
        foreach ($this->tempPhotos as $photo) {
            $this->form->photos[] = $photo;
        }

        // 5. Bersihkan array temporary
        $this->reset('tempPhotos');
    }

    /**
     * Menghapus foto dari antrean berdasarkan index.
     * Meneruskan perintah ke method di dalam Form Object.
     *
     * @param int $index
     */
    public function removePhoto($index)
    {
        $this->form->removePhoto($index);
    }

    // ==========================================
    // SELECTION HANDLERS
    // ==========================================

    /**
     * Menangani pemilihan aset dari hasil pencarian dropdown.
     *
     * @param int $id ID Aset
     * @param string $displayText Label nama aset
     * @param string|null $image URL gambar aset
     */
    public function selectAsset($id, $displayText, $image = null)
    {
        // 1. Update ID di Form Object
        $this->form->asset_id = $id;
        
        // 2. Update tampilan UI
        $this->selectedAssetDisplay = $displayText;
        $this->selectedAssetImage = $image;
        
        // 3. Reset pencarian
        $this->reset('searchAsset'); 
    }

    // ==========================================
    // PERSISTENCE
    // ==========================================

    /**
     * Mengeksekusi penyimpanan data ke database.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save()
    {
        // 1. Delegasikan penyimpanan ke Form Object
        $this->form->store();
        
        // 2. Berikan umpan balik dan redirect
        session()->flash('success', 'Data pemeliharaan berhasil ditambahkan');
        return redirect()->route('admin.maintenances.index'); 
    }

    // ==========================================
    // RENDER LOGIC
    // ==========================================

    /**
     * Merender tampilan komponen.
     * Melakukan query data aset dan teknisi yang diperlukan untuk dropdown.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // 1. Query Data Aset (Optimasi Select & Eager Loading)
        $assets = Asset::query()
            ->select('id', 'asset_tag', 'asset_model_id', 'image') 
            ->with('model:id,name,image') 
            // 2. Terapkan Filter Pencarian (Tag atau Nama Model)
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

        // 3. Ambil Data Teknisi
        $technicians = User::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.maintenance.maintenance-create', [
            'assets' => $assets,
            'technicians' => $technicians, 
        ]);
    }
}