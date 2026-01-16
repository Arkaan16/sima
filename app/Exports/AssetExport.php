<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Agar lebar kolom otomatis
use Maatwebsite\Excel\Concerns\WithStyles; // Agar header bold & rapi
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Class AssetExport
 * * Menangani proses ekspor data aset ke format Excel.
 * Mengimplementasikan antarmuka Maatwebsite untuk kustomisasi query (FromCollection),
 * format baris (WithMapping), header (WithHeadings), dan styling visual (WithStyles).
 */
class AssetExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $category;
    private $rowNumber = 0; // Variabel untuk nomor urut

    /**
     * Konstruktor untuk inisialisasi filter kategori.
     * * @param string|null $category Nama kategori untuk memfilter data aset (opsional).
     */
    public function __construct($category = null)
    {
        $this->category = $category;
    }

    /**
     * Mengambil koleksi data aset dari database (Query Builder).
     * * Logika Kunci:
     * 1. Menggunakan Eager Loading ('with') untuk mengambil relasi model, lokasi, dan supplier
     * guna mencegah masalah N+1 query dan meningkatkan performa ekspor.
     * 2. Menerapkan filter kondisional berdasarkan kategori yang dipilih user.
     * * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Asset::query()
            ->with([
                'model.category',
                'model.manufacturer',
                'defaultLocation.parent', // Load parent untuk mendapatkan nama Gedung
                'status',
                'supplier',
                'assignedTo' // Eager load ini juga agar performa lebih cepat
            ]);

        // Filter berdasarkan kategori jika tidak memilih 'Semua Kategori'
        if ($this->category && $this->category !== 'Semua Kategori') {
            $query->whereHas('model.category', function ($q) {
                $q->where('name', $this->category);
            });
        }

        return $query->get();
    }

    /**
     * Mendefinisikan baris judul (Header) pada file Excel.
     * * @return array Daftar nama kolom.
     */
    public function headings(): array
    {
        return [
            'No',
            'Kode Aset',
            'Nomor Seri',
            'Nama Model',
            'Kategori',
            'Pabrikan',
            'Status',
            'Lokasi Utama',
            'Lokasi Sub Ruangan',
            'Pemasok',
            'Nomor Order',
            'Tanggal Pembelian',
            'Harga Pembelian',
            'Garansi (Bulan)',
            'Tanggal Habis Masa Pakai (EOL)',
            'Ditugaskan Kepada',
        ];
    }

    /**
     * Memetakan (Mapping) setiap objek aset menjadi satu baris array untuk Excel.
     * * Logika Kunci:
     * 1. Penanggung Jawab (Polymorphic): Menangani pengecekan tipe relasi (Karyawan vs Lokasi/Lainnya)
     * dan fallback jika data relasi terhapus.
     * 2. Hierarki Lokasi: Memisahkan antara Lokasi Utama (Gedung/Parent) dan Sub Ruangan.
     * 3. Kalkulasi Garansi: Menghitung sisa bulan secara dinamis berdasarkan tanggal pembelian
     * dan durasi garansi (menangani status 'Habis', 'Sisa', atau '< 1 Bulan').
     * * @param mixed $asset Objek model Asset.
     * @return array Data yang sudah diformat untuk baris Excel.
     */
    public function map($asset): array
    {
        $this->rowNumber++;

        // --- 1. Logika Penanggung Jawab (Polymorphic) ---
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

        // --- 2. Logika Hierarki Lokasi ---
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

        // --- 3. Logika Perhitungan Sisa Garansi ---
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
            $this->rowNumber,
            $asset->asset_tag ?? '-',
            $asset->serial ?? '-',
            optional($asset->model)->name ?? '-',
            optional($asset->model->category)->name ?? '-',
            optional($asset->model->manufacturer)->name ?? '-',
            optional($asset->status)->name ?? '-',
            $namaLokasi,
            $namaSubRuangan,
            optional($asset->supplier)->name ?? '-',
            $asset->order_number ?? '-',
            $asset->purchase_date ? $asset->purchase_date->format('d-m-Y') : '-',
            $asset->purchase_cost ? 'Rp ' . number_format($asset->purchase_cost, 0, ',', '.') : '-',
            $garansiInfo,
            $asset->eol_date ? $asset->eol_date->format('d-m-Y') : '-',
            $assignedName,
        ];
    }

    /**
     * Menerapkan styling visual pada worksheet Excel.
     * * Mengatur:
     * - Header menjadi Bold dan rata tengah.
     * - Kolom spesifik (No, Tanggal, Garansi) menjadi rata tengah agar lebih mudah dibaca.
     * * @param Worksheet $sheet
     * @return array Konfigurasi style.
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // 1. Header (Baris 1) Bold & Center
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],

            // 2. Kolom No (A) Center
            'A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            
            // 3. Kolom Tanggal (L & O) Center
            'L' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'O' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],

            // 4. Kolom Garansi (N) Center
            'N' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }
}