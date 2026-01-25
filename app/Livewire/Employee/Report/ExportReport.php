<?php

namespace App\Livewire\Employee\Report;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\Maintenance;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeeMaintenanceExport; // Menggunakan Export Class yang baru
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

// Pastikan layout mengarah ke layout employee (sesuaikan jika berbeda)
#[Layout('components.layouts.employee')] 
#[Title('Ekspor Laporan Karyawan')]
class ExportReport extends Component
{
    // Hapus $selectedDataType karena otomatis 'pemeliharaan'
    public $selectedFormat = 'pdf';
    public $selectedMonth = '';
    
    public $months = [];
    public $previewData = [];

    protected $casts = [
        'execution_date' => 'date',
    ];

    public function mount()
    {
        Carbon::setLocale('id');
        $this->initializeMonths();
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

    public function updatedSelectedMonth()
    {
        $this->loadPreviewData();
    }

    public function loadPreviewData()
    {
        if ($this->selectedMonth) {
            $this->previewData = $this->getMaintenanceData();
        } else {
            $this->previewData = [];
        }
    }

    protected function getMaintenanceData()
    {
        $year = (int) substr($this->selectedMonth, 0, 4);
        $month = (int) substr($this->selectedMonth, 5, 2);
        $user = Auth::user();

        // Query dengan Filter Karyawan Login
        $maintenances = Maintenance::with(['asset.model', 'technicians'])
            ->whereYear('execution_date', $year)
            ->whereMonth('execution_date', $month)
            // Filter Khusus: Hanya data milik user login
            ->whereHas('technicians', function($q) use ($user) {
                // SESUAIKAN RELASI DI SINI
                $q->where('user_id', $user->id); 
            })
            ->orderBy('execution_date', 'asc')
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

    public function export()
    {
        if (!$this->selectedMonth) {
            session()->flash('error', 'Mohon pilih bulan laporan terlebih dahulu.');
            return;
        }

        if (empty($this->previewData)) {
            $this->loadPreviewData(); 
            if (empty($this->previewData)) {
                session()->flash('error', 'Tidak ada data pemeliharaan Anda pada bulan ini.');
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
            'type' => 'pemeliharaan', // Hardcode
            'month' => $this->getMonthLabel($this->selectedMonth),
            'category' => null,
        ];

        // Menggunakan view PDF yang sama dengan admin (reusable)
        $pdf = Pdf::loadView('exports.report-pdf', $data)
            ->setPaper('a4', 'landscape');
        
        $filename = 'Laporan-Karyawan-' . $this->selectedMonth . '.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $filename);
    }

    protected function exportExcel()
    {
        $filename = 'Laporan-Karyawan-' . $this->selectedMonth . '.xlsx';

        // Menggunakan Class Export Baru
        return Excel::download(
            new EmployeeMaintenanceExport($this->selectedMonth), 
            $filename
        );
    }

    protected function getMonthLabel($value)
    {
        $month = collect($this->months)->firstWhere('value', $value);
        return $month ? $month['label'] : $value;
    }

    public function render()
    {
        return view('livewire.employee.report.export-report');
    }
}