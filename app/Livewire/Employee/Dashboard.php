<?php

namespace App\Livewire\Employee;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use App\Models\Asset;
use App\Models\AssetStatus;
use App\Models\Maintenance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

#[Layout('components.layouts.employee')]
#[Title('Dashboard Teknisi')]
class Dashboard extends Component
{
    // === KARTU ATAS (STATS) ===
    public $totalAssets;         // Global
    public $myTotalMaintenances; // Personal
    public $myMonthMaintenances; // Personal

    // === PROGRESS BAR (STATUS GLOBAL) ===
    public $assetsByStatus = [];

    // === BAR CHART (RIWAYAT 7 HARI PERSONAL) ===
    public $completeHistory = []; // Penamaan variabel disamakan dengan Admin agar konsisten

    // === PIE CHART (TIPE PEKERJAAN PERSONAL) ===
    public $maintenanceChartData = [
        'labels' => [],
        'series' => []
    ];

    /**
     * Menginisialisasi komponen saat pertama kali dimuat.
     * Fungsi ini memicu pemuatan seluruh data yang diperlukan untuk dashboard.
     *
     * @return void
     */
    public function mount()
    {
        $this->loadStats();
        $this->loadAssetsByStatus();     // Card 2
        $this->loadMaintenanceHistory(); // Card 3
        $this->loadMaintenanceChart();   // Card 4 (Kanan)
    }

    /**
     * Memuat statistik angka untuk kartu bagian atas dashboard.
     * Mengambil total aset (global) dan statistik pekerjaan spesifik milik teknisi yang login.
     *
     * @return void
     */
    public function loadStats()
    {
        $userId = Auth::id();

        // 1. Total Aset (Global)
        // Menggunakan Cache selama 60 detik untuk mengurangi beban query ke database karena data ini bersifat global.
        $this->totalAssets = Cache::remember('global_assets_count', 60, fn() => Asset::count());

        // 2. Total Pekerjaan Saya (Seumur Hidup)
        // Menggunakan whereHas untuk memfilter maintenance yang hanya ditugaskan ke user yang sedang login.
        $this->myTotalMaintenances = Maintenance::whereHas('technicians', function($q) use ($userId) {
            $q->where('users.id', $userId);
        })->count();

        // 3. Pekerjaan Bulan Ini
        // Filter tambahan berdasarkan bulan dan tahun saat ini untuk melihat kinerja bulan berjalan.
        $this->myMonthMaintenances = Maintenance::whereHas('technicians', function($q) use ($userId) {
            $q->where('users.id', $userId);
        })->whereMonth('execution_date', now()->month)
          ->whereYear('execution_date', now()->year)
          ->count();
    }

    /**
     * Memuat data jumlah aset berdasarkan statusnya (contoh: Baik, Rusak, Sedang Perbaikan).
     * Data ini digunakan untuk komponen Progress Bar.
     *
     * @return void
     */
    public function loadAssetsByStatus()
    {
        // Sama persis logic Admin (Global Status)
        // Mengambil semua status aset beserta jumlah aset yang terkait (withCount),
        // lalu memetakan hasilnya ke dalam format array sederhana untuk frontend.
        $this->assetsByStatus = AssetStatus::withCount('assets')
            ->get()
            ->map(function ($status) {
                return [
                    'status' => $status,
                    'count' => $status->assets_count
                ];
            })->toArray();
    }

    /**
     * Memuat riwayat pekerjaan teknisi selama 7 hari terakhir.
     * Menghasilkan data untuk Bar Chart, termasuk mengisi hari yang tidak ada pekerjaannya dengan nilai 0.
     *
     * @return void
     */
    public function loadMaintenanceHistory()
    {
        // Riwayat 7 Hari (Personal)
        $userId = Auth::id();
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(6);

        // Mengelompokkan data berdasarkan tanggal eksekusi dan menghitung jumlahnya per hari.
        // Hasilnya berupa array key-value [ '2023-01-01' => 5, ... ]
        $historyData = Maintenance::selectRaw('DATE(execution_date) as date, count(*) as count')
            ->whereHas('technicians', function($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->whereBetween('execution_date', [
                $startDate->format('Y-m-d 00:00:00'), 
                $endDate->format('Y-m-d 23:59:59')
            ])
            ->groupBy('date')
            ->pluck('count', 'date');

        $days = [];
        // Loop mundur dari hari ke-6 sampai hari ini (0).
        // Tujuannya untuk memastikan grafik tetap menampilkan tanggal meskipun tidak ada data (count = 0) pada hari tersebut.
        for ($i = 6; $i >= 0; $i--) {
            $dateKey = $endDate->copy()->subDays($i)->format('Y-m-d');
            $days[] = [
                'date' => $dateKey,
                'count' => $historyData[$dateKey] ?? 0 
            ];
        }

        $this->completeHistory = collect($days);
    }

    /**
     * Memuat proporsi tipe pekerjaan (Maintenance Type) yang dilakukan teknisi.
     * Data ini digunakan untuk Pie Chart.
     *
     * @return void
     */
    public function loadMaintenanceChart()
    {
        // Pie Chart Tipe Pekerjaan (Personal)
        $userId = Auth::id();
        
        // Menghitung jumlah pekerjaan berdasarkan tipe (maintenance_type) khusus untuk user ini.
        $stats = Maintenance::whereHas('technicians', function($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->select('maintenance_type', DB::raw('count(*) as total'))
            ->groupBy('maintenance_type')
            ->get();

        $typeLabels = Maintenance::getTypes(); 
        $labels = [];
        $series = [];

        // Memisahkan label dan data angka ke dalam dua array terpisah sesuai kebutuhan library chart frontend.
        // Juga mengonversi kode tipe ke label yang mudah dibaca (jika tersedia).
        foreach ($stats as $stat) {
            $labels[] = $typeLabels[$stat->maintenance_type] ?? $stat->maintenance_type;
            $series[] = $stat->total;
        }

        $this->maintenanceChartData = [
            'labels' => $labels,
            'series' => $series
        ];
    }

    /**
     * Merender tampilan dashboard dan mengirimkan data tambahan ke view.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        $userId = Auth::id();

        // Tabel 10 Riwayat Terakhir (Personal)
        // Menggunakan Eager Loading (with) untuk mengambil relasi aset, model, lokasi, dan gedung parent
        // guna mencegah masalah performa N+1 Query saat ditampilkan di tabel.
        $latestMaintenances = Maintenance::with([
                'asset', 
                'asset.model', 
                'asset.defaultLocation.parent' // LOAD LOKASI & PARENT (GEDUNG)
            ])
            ->whereHas('technicians', function($q) use ($userId) {
                $q->where('users.id', $userId);
            })
            ->latest('execution_date')
            ->take(10)
            ->get();

        return view('livewire.employee.dashboard', [
            'latestMaintenances' => $latestMaintenances
        ]);
    }
}