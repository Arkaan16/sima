<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\User;
use App\Models\Maintenance;
use App\Models\AssetStatus;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

#[Layout('components.layouts.admin')]
#[Title('Dashboard Admin')]
class Dashboard extends Component
{
    use WithPagination;

    // === VARIABEL LAMA ===
    public $totalAssets;
    public $totalUsers;
    public $totalMaintenances;
    public $assetsByStatus = [];
    public $completeHistory = [];

    // === VARIABEL BARU (STATISTIK LOKASI) ===
    public $searchLocation = '';

    // === VARIABEL BARU (PIE CHART) ===
    public $maintenanceChartData = [
        'labels' => [],
        'series' => []
    ];

    public function mount()
    {
        // Memanggil fungsi-fungsi statistik
        $this->loadStats();
        $this->loadAssetsByStatus();
        $this->loadMaintenanceHistory();
        
        // Memanggil fungsi chart baru
        $this->loadMaintenanceChart();
    }

    // =================================================================
    // 1. FUNGSI LAMA (DIPULIHKAN KEMBALI)
    // =================================================================

    public function loadStats()
    {
        $this->totalAssets = Cache::remember('dashboard_total_assets', 60, fn() => Asset::count());
        $this->totalUsers = Cache::remember('dashboard_total_users', 60, fn() => User::count());
        $this->totalMaintenances = Cache::remember('dashboard_total_maintenances', 60, fn() => Maintenance::count());
    }

    public function loadAssetsByStatus()
    {
        $this->assetsByStatus = AssetStatus::withCount('assets')
            ->get()
            ->map(function ($status) {
                return [
                    'status' => $status,
                    'count' => $status->assets_count
                ];
            })->toArray();
    }

    public function loadMaintenanceHistory()
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(6);

        $historyData = Maintenance::selectRaw('DATE(execution_date) as date, count(*) as count')
            ->whereBetween('execution_date', [
                $startDate->format('Y-m-d 00:00:00'), 
                $endDate->format('Y-m-d 23:59:59')
            ])
            ->groupBy('date')
            ->pluck('count', 'date');

        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateKey = $endDate->copy()->subDays($i)->format('Y-m-d');
            
            $days[] = [
                'date' => $dateKey,
                'count' => $historyData[$dateKey] ?? 0 
            ];
        }

        $this->completeHistory = collect($days);
    }

    // =================================================================
    // 2. FUNGSI BARU (PIE CHART MAINTENANCE)
    // =================================================================

    public function loadMaintenanceChart()
    {
        // Ambil jumlah data group by tipe pemeliharaan
        $stats = Maintenance::select('maintenance_type', DB::raw('count(*) as total'))
            ->groupBy('maintenance_type')
            ->get();

        // Ambil label text yang bisa dibaca (dari helper Model Maintenance)
        // Pastikan Model Maintenance memiliki method getTypes() sesuai instruksi sebelumnya
        // Jika tidak ada, gunakan array manual di sini.
        $typeLabels = Maintenance::getTypes(); 

        $labels = [];
        $series = [];

        foreach ($stats as $stat) {
            $labels[] = $typeLabels[$stat->maintenance_type] ?? $stat->maintenance_type;
            $series[] = $stat->total;
        }

        $this->maintenanceChartData = [
            'labels' => $labels,
            'series' => $series
        ];
    }

    // =================================================================
    // 3. FUNGSI BARU (PENCARIAN LOKASI)
    // =================================================================

    public function updatingSearchLocation()
    {
        $this->resetPage();
    }

    public function resetSearch()
    {
        $this->searchLocation = '';
        $this->resetPage();
    }

    // =================================================================
    // 4. HELPER: Hitung Total Aset Termasuk Sub-Lokasi
    // =================================================================
    
    private function getTotalAssetsWithChildren($location)
    {
        // Hitung aset langsung di lokasi ini
        $directAssets = $location->assets->count();
        
        // Hitung aset di semua sub-lokasi (children)
        $childrenAssets = 0;
        if ($location->children) {
            foreach ($location->children as $child) {
                $childrenAssets += $this->getTotalAssetsWithChildren($child);
            }
        }
        
        return $directAssets + $childrenAssets;
    }

    // =================================================================
    // 5. RENDER
    // =================================================================

    public function render()
    {
        // Query Lokasi dengan Optimasi Eager Loading
        $locations = Location::query()
            ->with([
                'parent', 
                'assets.model.category',
                'children.assets.model.category', // Load children untuk perhitungan
            ]) 
            ->when($this->searchLocation, function ($query) {
                $query->where('name', 'like', '%' . $this->searchLocation . '%')
                      ->orWhereHas('parent', function($q) {
                          $q->where('name', 'like', '%' . $this->searchLocation . '%');
                      });
            })
            ->orderBy('name', 'asc')
            ->paginate(10);

        // Tambahkan computed property untuk total assets (termasuk children)
        $locations->getCollection()->transform(function ($location) {
            $location->total_assets_with_children = $this->getTotalAssetsWithChildren($location);
            return $location;
        });

        return view('livewire.admin.dashboard', [
            'paginatedLocations' => $locations
        ]);
    }
}