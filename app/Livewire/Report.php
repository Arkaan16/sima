<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\Asset;
use App\Models\Maintenance;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssetExport;
use App\Exports\MaintenanceExport;
use App\Exports\EmployeeMaintenanceExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

#[Title('Ekspor Laporan')]
class Report extends Component
{
    // =================================================================
    // PROPERTIES & STATE
    // =================================================================

    public $isAdmin = false; // Penanda Role

    public $selectedDataType = ''; // 'aset' atau 'pemeliharaan'
    public $selectedFormat = 'pdf';
    public $selectedMonth = '';
    public $selectedCategory = '';

    public $assetCategories = [];
    public $months = [];
    public $previewData = [];

    protected $casts = [
        'execution_date' => 'date',
    ];

    // =================================================================
    // LIFECYCLE HOOKS
    // =================================================================

    public function mount()
    {
        Carbon::setLocale('id');

        // 1. Tentukan Role (Sesuaikan logika ini dengan sistem Role Anda)
        // Contoh: Cek apakah user punya akses ke layout admin atau role tertentu
        $user = Auth::user();
        // Asumsi: Admin memiliki role 'admin' atau properti is_admin (sesuaikan kondisi if ini)
        $this->isAdmin = $user->role === 'admin' || $user->is_admin; 
        
        // 2. Inisialisasi Data Dasar
        $this->initializeMonths();

        // 3. Konfigurasi Awal Berdasarkan Role
        if ($this->isAdmin) {
            $this->initializeCategories();
        } else {
            // Jika Employee: Paksa tipe data 'pemeliharaan' dan kunci kategori
            $this->selectedDataType = 'pemeliharaan';
            $this->selectedCategory = '';
        }
    }

    public function render()
    {
        return view('livewire.report');
    }

    // =================================================================
    // UPDATERS
    // =================================================================

    public function updatedSelectedDataType()
    {
        $this->selectedMonth = '';
        $this->selectedCategory = '';
        $this->previewData = [];
        $this->resetErrorBag();
    }

    public function updatedSelectedMonth()
    {
        $this->loadPreviewData();
    }

    public function updatedSelectedCategory()
    {
        $this->loadPreviewData();
    }

    // =================================================================
    // DATA INITIALIZATION
    // =================================================================

    protected function initializeMonths()
    {
        $this->months = [];
        $now = Carbon::now();
        $currentYear = $now->year;
        $startYear = $currentYear - 1;

        for ($year = $currentYear; $year >= $startYear; $year--) {
            for ($month = 12; $month >= 1; $month--) {
                if ($year == $currentYear && $month > $now->month) {
                    continue;
                }
                $date = Carbon::createFromDate($year, $month, 1);
                $this->months[] = [
                    'value' => $date->format('Y-m'),
                    'label' => $date->translatedFormat('F Y'),
                ];
            }
        }
    }

    protected function initializeCategories()
    {
        $this->assetCategories = ['Semua Kategori'];
        
        $categories = Asset::with('model.category')
            ->get()
            ->pluck('model.category.name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
        
        $this->assetCategories = array_merge($this->assetCategories, $categories);
    }

    // =================================================================
    // DATA LOGIC (PREVIEW)
    // =================================================================

    public function loadPreviewData()
    {
        if ($this->selectedDataType === 'pemeliharaan' && $this->selectedMonth) {
            $this->previewData = $this->getMaintenanceData();
        } 
        elseif ($this->selectedDataType === 'aset' && $this->selectedCategory && $this->isAdmin) {
            $this->previewData = $this->getAssetData();
        } 
        else {
            $this->previewData = [];
        }
    }

    protected function getMaintenanceData()
    {
        $year = (int) substr($this->selectedMonth, 0, 4);
        $month = (int) substr($this->selectedMonth, 5, 2);
        
        $query = Maintenance::with(['asset.model', 'technicians'])
            ->whereYear('execution_date', $year)
            ->whereMonth('execution_date', $month);

        // LOGIKA PENTING: Jika bukan Admin (Employee), filter berdasarkan user login
        if (!$this->isAdmin) {
            $user = Auth::user();
            $query->whereHas('technicians', function($q) use ($user) {
                $q->where('user_id', $user->id); 
            });
            $query->orderBy('execution_date', 'asc');
        } else {
            $query->orderBy('execution_date', 'desc');
        }

        $maintenances = $query->get();

        if ($maintenances->isEmpty()) return [];

        return $maintenances->map(function ($maintenance, $key) {
            $assetTag = $maintenance->asset?->asset_tag ?? '-';
            $assetName = $maintenance->asset?->model?->name ?? '-';
            
            $technicians = $maintenance->technicians->isNotEmpty() 
                ? $maintenance->technicians->pluck('name')->implode(', ') 
                : '-';

            return [
                'no' => $key + 1,
                'tanggal' => $maintenance->execution_date->format('d-m-Y'),
                'aset' => "{$assetTag} - {$assetName}",
                'judul' => $maintenance->title,
                'jenis' => $maintenance->type_label,
                'Deskripsi Pekerjaan'=> $maintenance->description,
                'Karyawan' => $technicians,
            ];
        })->toArray();
    }

    protected function getAssetData()
    {
        // Fitur ini khusus Admin
        if (!$this->isAdmin) return [];

        $query = Asset::query()
            ->with([
                'model.category', 'model.manufacturer', 'defaultLocation.parent', 
                'status', 'supplier'
            ]);

        if ($this->selectedCategory && $this->selectedCategory !== 'Semua Kategori') {
            $query->whereHas('model.category', function ($q) {
                $q->where('name', $this->selectedCategory);
            });
        }

        return $query->get()->map(function ($asset, $index) {
            $asset->loadMissing('assignedTo');
            
            // Logika Penamaan Penanggung Jawab
            $assignedName = 'Milik Ruangan';
            if ($asset->assigned_to_id) {
                $isEmployee = in_array($asset->assigned_to_type, [
                    'App\Models\Employee', '\App\Models\Employee', 'App\\Models\\Employee', \App\Models\Employee::class
                ]);
                
                if ($isEmployee) {
                    $assignedName = $asset->assignedTo ? $asset->assignedTo->name : 
                        (\App\Models\Employee::find($asset->assigned_to_id)->name ?? 'Data Karyawan Terhapus');
                } else {
                    $assignedName = $asset->assignedTo?->name ?? 'Data Terhapus';
                }
            }

            // Logika Lokasi
            $namaLokasi = '-';
            $namaSubRuangan = '-';
            if ($asset->defaultLocation) {
                if ($asset->defaultLocation->parent) {
                    $namaLokasi = $asset->defaultLocation->parent->name;
                    $namaSubRuangan = $asset->defaultLocation->name;
                } else {
                    $namaLokasi = $asset->defaultLocation->name;
                }
            }

            // Logika Garansi
            $garansiInfo = $asset->warranty_months ? "{$asset->warranty_months} Bulan" : '-';
            if ($asset->purchase_date && $asset->warranty_months) {
                $expiredDate = $asset->purchase_date->copy()->addMonths($asset->warranty_months);
                if (now()->greaterThan($expiredDate)) {
                    $garansiInfo = "{$asset->warranty_months} Bulan (Habis)";
                } else {
                    $sisaBulan = (int) round(now()->floatDiffInMonths($expiredDate));
                    $garansiInfo = $sisaBulan == 0 ? "{$asset->warranty_months} Bulan (< 1 Bln)" : "{$asset->warranty_months} Bulan (Sisa {$sisaBulan} Bln)";
                }
            }
            
            return [
                'No' => $index + 1,
                'Kode Aset' => $asset->asset_tag ?? '-',
                'Nomor Seri' => $asset->serial ?? '-',
                'Nama Model' => optional($asset->model)->name ?? '-',
                'Kategori' => optional($asset->model->category)->name ?? '-',
                'Pabrikan' => optional($asset->model->manufacturer)->name ?? '-',
                'Status' => optional($asset->status)->name ?? '-',
                'Lokasi Utama' => $namaLokasi,
                'Lokasi Sub Ruangan' => $namaSubRuangan,
                'Pemasok' => optional($asset->supplier)->name ?? '-',
                'Nomor Order' => $asset->order_number ?? '-',
                'Tanggal Pembelian' => $asset->purchase_date ? $asset->purchase_date->format('d-m-Y') : '-',
                'Harga Pembelian' => $asset->purchase_cost ? 'Rp ' . number_format($asset->purchase_cost, 0, ',', '.') : '-',
                'Garansi (Bulan)' => $garansiInfo,
                'Tanggal Habis Masa Pakai (EOL)' => $asset->eol_date ? $asset->eol_date->format('d-m-Y') : '-',
                'Penanggung Jawab Aset' => $assignedName,
            ];
        })->toArray();
    }

    // =================================================================
    // EXPORT LOGIC
    // =================================================================

    public function export()
    {
        // Validasi
        if (!$this->selectedDataType) {
            session()->flash('error', 'Mohon pilih jenis laporan terlebih dahulu.');
            return;
        }

        if ($this->selectedDataType === 'pemeliharaan' && !$this->selectedMonth) {
            session()->flash('error', 'Mohon pilih bulan laporan terlebih dahulu.');
            return;
        }
        
        if ($this->selectedDataType === 'aset' && !$this->selectedCategory) {
            session()->flash('error', 'Mohon pilih kategori aset terlebih dahulu.');
            return;
        }

        if (empty($this->previewData)) {
            $this->loadPreviewData(); 
            if (empty($this->previewData)) {
                session()->flash('error', 'Tidak ada data untuk diekspor pada periode/kategori ini.');
                return;
            }
        }

        if ($this->selectedFormat === 'pdf') {
            return $this->exportPdf();
        } else {
            return $this->exportExcel();
        }
    }

    protected function exportPdf()
    {
        $data = [
            'data' => $this->previewData,
            'type' => $this->selectedDataType,
            'month' => $this->selectedMonth ? $this->getMonthLabel($this->selectedMonth) : null,
            'category' => $this->selectedCategory,
        ];

        $pdf = Pdf::loadView('exports.report-pdf', $data)
            ->setPaper('a4', 'landscape');
        
        $rolePrefix = $this->isAdmin ? '' : 'Karyawan-';
        $filename = $this->selectedDataType === 'pemeliharaan'
            ? 'Laporan-Pemeliharaan-' . $rolePrefix . $this->selectedMonth . '.pdf'
            : 'Laporan-Aset-' . date('Y-m-d') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    protected function exportExcel()
    {
        $rolePrefix = $this->isAdmin ? '' : 'Karyawan-';
        $filename = $this->selectedDataType === 'pemeliharaan'
            ? 'Laporan-Pemeliharaan-' . $rolePrefix . $this->selectedMonth . '.xlsx'
            : 'Laporan-Aset-' . date('Y-m-d') . '.xlsx';

        if ($this->selectedDataType === 'pemeliharaan') {
            // LOGIKA EXPORT EXCEL:
            // Jika Admin -> Pakai MaintenanceExport (Semua Data)
            // Jika Employee -> Pakai EmployeeMaintenanceExport (Data Sendiri)
            if ($this->isAdmin) {
                return Excel::download(new MaintenanceExport($this->selectedMonth), $filename);
            } else {
                return Excel::download(new EmployeeMaintenanceExport($this->selectedMonth), $filename);
            }
        } else {
            $categoryParam = ($this->selectedCategory !== 'Semua Kategori') ? $this->selectedCategory : null;
            return Excel::download(new AssetExport($categoryParam), $filename);
        }
    }

    protected function getMonthLabel($value)
    {
        $month = collect($this->months)->firstWhere('value', $value);
        return $month ? $month['label'] : $value;
    }
}