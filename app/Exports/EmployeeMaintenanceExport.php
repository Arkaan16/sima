<?php

namespace App\Exports;

use App\Models\Maintenance;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class EmployeeMaintenanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $month;
    private $rowNumber = 0;

    public function __construct($month)
    {
        $this->month = $month;
    }

    public function collection()
    {
        $user = Auth::user();

        // Query dasar
        return Maintenance::with(['asset.model', 'technicians'])
            // Filter Tahun & Bulan
            ->whereYear('execution_date', substr($this->month, 0, 4))
            ->whereMonth('execution_date', substr($this->month, 5, 2))
            
            // --- FILTER KHUSUS EMPLOYEE ---
            // Hanya ambil maintenance di mana user yang login terdaftar sebagai teknisi
            ->whereHas('technicians', function($q) use ($user) {
                // ASUMSI: Relasi teknisi ke user menggunakan kolom 'user_id'
                // Jika Anda menggunakan 'employee_id', silakan sesuaikan:
                // $q->where('id', $user->employee->id); 
                $q->where('user_id', $user->id); 
            })
            
            ->orderBy('execution_date', 'asc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Aset',
            'Judul',
            'Jenis Pemeliharaan',
            'Deskripsi Pekerjaan',
            'Karyawan',
        ];
    }

    public function map($maintenance): array
    {
        $this->rowNumber++;

        $assetTag = $maintenance->asset->asset_tag ?? '-';
        $assetName = $maintenance->asset->model->name ?? '-';

        $technicians = $maintenance->technicians->isNotEmpty()
            ? $maintenance->technicians->pluck('name')->implode(', ')
            : '-';

        return [
            $this->rowNumber,
            $maintenance->execution_date->format('d-m-Y'),
            "{$assetTag} - {$assetName}",
            $maintenance->title,
            $maintenance->type_label,
            $maintenance->description,
            $technicians,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            'A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'B' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'E' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }
}