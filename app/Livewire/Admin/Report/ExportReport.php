<?php

namespace App\Livewire\Admin\Report;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\Asset;
use App\Models\Maintenance;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AssetExport;
use App\Exports\MaintenanceExport;
use Carbon\Carbon;

#[Layout('components.layouts.admin')]
#[Title('Ekspor Laporan')]
class ExportReport extends Component
{
    // =================================================================
    // PROPERTIES & STATE
    // =================================================================

    public $selectedDataType = '';
    public $selectedFormat = 'pdf';
    public $selectedMonth = '';

    /** * PERBAIKAN 1: Default harus string KOSONG (''), 
     * JANGAN diisi 'Semua Kategori' agar dropdown tidak otomatis memilihnya.
     */
    public $selectedCategory = ''; 

    public $assetCategories = [];
    public $months = [];
    public $previewData = [];

    protected $casts = [
        'execution_date' => 'date',
    ];

    // =================================================================
    // LIFECYCLE HOOKS (INITIALIZATION)
    // =================================================================

    public function mount()
    {
        Carbon::setLocale('id');
        $this->initializeMonths();
        $this->initializeCategories();
    }

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
        // Kita tetap memasukkan opsi 'Semua Kategori' ke dalam list pilihan
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
    // LIFECYCLE HOOKS (UPDATES)
    // =================================================================

    /**
     * PERBAIKAN 2: Saat tipe laporan berubah (klik tombol Pemeliharaan/Aset),
     * kita PAKSA selectedCategory jadi KOSONG ('').
     */
    public function updatedSelectedDataType()
    {
        $this->selectedMonth = '';
        
        // RESET KE KOSONG (Bukan 'Semua Kategori')
        $this->selectedCategory = ''; 
        
        $this->previewData = [];
        $this->resetErrorBag(); 
        
        // PERBAIKAN 3: Hapus logika "autoload" untuk aset.
        // Biarkan kosong sampai user memilih dropdown.
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
    // DATA LOGIC
    // =================================================================

    public function loadPreviewData()
    {
        // Kondisi: Hanya load jika input sudah dipilih (tidak kosong)
        if ($this->selectedDataType === 'pemeliharaan' && $this->selectedMonth) {
            $this->previewData = $this->getMaintenanceData();
        } 
        elseif ($this->selectedDataType === 'aset' && $this->selectedCategory) {
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
        
        $maintenances = Maintenance::with(['asset.model', 'technicians'])
            ->whereYear('execution_date', $year)
            ->whereMonth('execution_date', $month)
            ->orderBy('execution_date', 'desc')
            ->get();

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
                'teknisi' => $technicians,
            ];
        })->toArray();
    }

    protected function getAssetData()
    {
        $query = Asset::query()
            ->with([
                'model.category',
                'model.manufacturer',
                'defaultLocation.parent', 
                'status',
                'supplier',
            ]);

        // Filter jika user memilih kategori spesifik (selain 'Semua Kategori')
        if ($this->selectedCategory && $this->selectedCategory !== 'Semua Kategori') {
            $query->whereHas('model.category', function ($q) {
                $q->where('name', $this->selectedCategory);
            });
        }

        return $query->get()->map(function ($asset, $index) {
            $asset->loadMissing('assignedTo');
            
            $assignedName = 'Milik Ruangan';
            if ($asset->assigned_to_id) {
                $isEmployee = in_array($asset->assigned_to_type, [
                    'App\Models\Employee', '\App\Models\Employee', 'App\\Models\\Employee', \App\Models\Employee::class
                ]);
                
                if ($isEmployee) {
                    if ($asset->assignedTo) {
                        $assignedName = $asset->assignedTo->name;
                    } else {
                        $employee = \App\Models\Employee::find($asset->assigned_to_id);
                        $assignedName = $employee ? $employee->name : 'Data Karyawan Terhapus';
                    }
                } else {
                    $assignedName = $asset->assignedTo?->name ?? 'Data Terhapus';
                }
            }

            $namaLokasi = '-';
            $namaSubRuangan = '-';

            if ($asset->defaultLocation) {
                if ($asset->defaultLocation->parent) {
                    $namaLokasi = $asset->defaultLocation->parent->name;
                    $namaSubRuangan = $asset->defaultLocation->name;
                } else {
                    $namaLokasi = $asset->defaultLocation->name;
                    $namaSubRuangan = '-'; 
                }
            }

            $garansiInfo = $asset->warranty_months ? "{$asset->warranty_months} Bulan" : '-';

            if ($asset->purchase_date && $asset->warranty_months) {
                $expiredDate = $asset->purchase_date->copy()->addMonths($asset->warranty_months);
                
                if (now()->greaterThan($expiredDate)) {
                    $garansiInfo = "{$asset->warranty_months} Bulan (Habis)";
                } else {
                    $sisaBulan = (int) round(now()->floatDiffInMonths($expiredDate));
                    if ($sisaBulan == 0) {
                        $garansiInfo = "{$asset->warranty_months} Bulan (< 1 Bln)";
                    } else {
                        $garansiInfo = "{$asset->warranty_months} Bulan (Sisa {$sisaBulan} Bln)";
                    }
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
                'Ditugaskan Kepada' => $assignedName,
            ];
        })->toArray();
    }

    public function export()
    {
        if (!$this->selectedDataType) {
            session()->flash('error', 'Mohon pilih jenis laporan terlebih dahulu.');
            return;
        }

        if ($this->selectedDataType === 'pemeliharaan' && !$this->selectedMonth) {
            session()->flash('error', 'Mohon pilih bulan laporan terlebih dahulu.');
            return;
        }
        
        // Validasi wajib pilih kategori (termasuk 'Semua Kategori')
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
        
        $filename = $this->selectedDataType === 'pemeliharaan'
            ? 'Laporan-Pemeliharaan-' . $this->selectedMonth . '.pdf'
            : 'Laporan-Aset-' . date('Y-m-d') . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    protected function exportExcel()
    {
        $filename = $this->selectedDataType === 'pemeliharaan'
            ? 'Laporan-Pemeliharaan-' . $this->selectedMonth . '.xlsx'
            : 'Laporan-Aset-' . date('Y-m-d') . '.xlsx';

        if ($this->selectedDataType === 'pemeliharaan') {
            return Excel::download(
                new MaintenanceExport($this->selectedMonth), 
                $filename
            );
        } else {
            $categoryParam = ($this->selectedCategory !== 'Semua Kategori') ? $this->selectedCategory : null;
            
            return Excel::download(
                new AssetExport($categoryParam), 
                $filename
            );
        }
    }

    protected function getMonthLabel($value)
    {
        $month = collect($this->months)->firstWhere('value', $value);
        return $month ? $month['label'] : $value;
    }

    public function render()
    {
        return view('livewire.admin.report.export-report');
    }
}