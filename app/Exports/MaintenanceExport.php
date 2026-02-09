<?php

namespace App\Exports;

use App\Models\Maintenance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Agar lebar kolom otomatis
use Maatwebsite\Excel\Concerns\WithStyles; // Agar bisa styling (Bold/Center)
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Class MaintenanceExport
 * 
 * Kelas untuk mengekspor data maintenance ke file Excel.
 * Mengimplementasikan berbagai interface dari Maatwebsite\Excel untuk:
 * - Mengambil data dari collection (FromCollection)
 * - Menambahkan header kolom (WithHeadings)
 * - Mapping data per baris (WithMapping)
 * - Auto-size lebar kolom (ShouldAutoSize)
 * - Styling cell Excel (WithStyles)
 * 
 * @package App\Exports
 */
class MaintenanceExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    /**
     * Bulan yang dipilih untuk filter data (format: YYYY-MM)
     * 
     * @var string
     */
    protected $month;
    
    /**
     * Nomor urut baris untuk kolom "No" pada Excel
     * Diincrement setiap kali map() dipanggil
     * 
     * @var int
     */
    private $rowNumber = 0; // Variabel untuk nomor urut manual

    /**
     * Constructor
     * 
     * @param string $month Format YYYY-MM untuk filter data berdasarkan bulan
     */
    public function __construct($month)
    {
        $this->month = $month;
    }

    /**
     * Mengambil collection data maintenance dari database
     * 
     * Filter berdasarkan tahun dan bulan dari parameter $month.
     * Eager loading relasi 'asset.model' dan 'technicians' untuk optimasi query.
     * Data diurutkan berdasarkan execution_date secara descending (terbaru di atas).
     * 
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Ambil data maintenance dengan relasi asset, model, dan technicians
        return Maintenance::with(['asset.model', 'technicians'])
            // Filter berdasarkan tahun (4 karakter pertama dari $month)
            ->whereYear('execution_date', substr($this->month, 0, 4))
            // Filter berdasarkan bulan (2 karakter setelah karakter ke-5)
            ->whereMonth('execution_date', substr($this->month, 5, 2))
            // Urutkan dari tanggal terbaru
            ->orderBy('execution_date', 'desc')
            ->get();
    }

    /**
     * Menentukan header kolom pada baris pertama Excel
     * 
     * @return array Daftar nama kolom header
     */
    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Aset',
            'Judul',
            'Jenis Pemeliharaan',
            'Deskripsi Pekerjaan', // Kolom baru sesuai PDF
            'Karyawan',
        ];
    }

    /**
     * Mapping data maintenance menjadi array untuk setiap baris Excel
     * 
     * Logika kunci:
     * - Increment nomor urut otomatis
     * - Format tanggal menjadi dd-mm-yyyy
     * - Gabungkan asset_tag dan nama model aset
     * - Handle null safety untuk relasi asset
     * - Gabungkan nama teknisi dengan koma jika lebih dari satu
     * 
     * @param \App\Models\Maintenance $maintenance Instance model Maintenance
     * @return array Data yang sudah dimapping untuk satu baris Excel
     */
    public function map($maintenance): array
    {
        // Increment nomor urut setiap baris
        $this->rowNumber++;

        // Logika Aset (Handle null safety)
        // Jika asset atau model tidak ada, tampilkan '-'
        $assetTag = $maintenance->asset->asset_tag ?? '-';
        $assetName = $maintenance->asset->model->name ?? '-';

        // Logika Teknisi (Gabung nama dengan koma)
        // Jika ada teknisi, ambil semua nama dan gabungkan dengan koma
        // Jika tidak ada teknisi, tampilkan '-'
        $technicians = $maintenance->technicians->isNotEmpty()
            ? $maintenance->technicians->pluck('name')->implode(', ')
            : '-';

        return [
            $this->rowNumber, // Kolom No
            $maintenance->execution_date->format('d-m-Y'), // Kolom Tanggal
            "{$assetTag} - {$assetName}", // Kolom Aset
            $maintenance->title, // Kolom Judul
            $maintenance->type_label, // Kolom Jenis
            $maintenance->description, // Kolom Deskripsi
            $technicians, // Kolom Teknisi
        ];
    }

    /**
     * Styling Excel agar tampilan lebih rapi dan profesional
     * 
     * Styling yang diterapkan:
     * - Baris 1 (Header): Bold, font size 12, rata tengah horizontal dan vertikal
     * - Kolom A (No): Rata tengah horizontal
     * - Kolom B (Tanggal): Rata tengah horizontal
     * - Kolom E (Jenis): Rata tengah horizontal
     * 
     * @param Worksheet $sheet Instance worksheet PhpSpreadsheet
     * @return array Array berisi konfigurasi styling
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // 1. Baris pertama (Header) dibuat Bold dan Center
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],

            // 2. Kolom A (No), B (Tanggal), E (Jenis) dibuat Rata Tengah (Center) agar rapi
            'A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'B' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'E' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }
}