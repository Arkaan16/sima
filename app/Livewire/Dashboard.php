<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use App\Models\Asset;
use App\Models\User;
use App\Models\Maintenance;
use App\Models\AssetStatus;
use App\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

#[Title('Dashboard')]
class Dashboard extends Component
{
    use WithPagination;

    // === VARIABEL SHARED (DIPAKAI KEDUANYA) ===
    public $totalAssets;
    public $assetsByStatus = [];
    public $completeHistory = []; // Data Grafik Batang
    public $maintenanceChartData = [ // Data Pie Chart
        'labels' => [],
        'series' => []
    ];

    // === VARIABEL KHUSUS ADMIN ===
    public $totalUsers;
    public $totalMaintenances; // Global count
    public $searchLocation = '';
    public $totalMaintenancesThisMonth;

    // === VARIABEL KHUSUS EMPLOYEE ===
    public $myTotalMaintenances;
    public $myMonthMaintenances;

    public function mount()
    {
        $user = Auth::user();

        // 1. LOAD DATA UMUM (Sama untuk semua)
        $this->loadSharedStats();

        // 2. LOAD DATA SPESIFIK ROLE
        if ($user->role === 'admin') {
            $this->loadAdminStats();
            $this->loadAdminHistory();
            $this->loadAdminChart();
        } else {
            $this->loadEmployeeStats($user->id);
            $this->loadEmployeeHistory($user->id);
            $this->loadEmployeeChart($user->id);
        }
    }

    // =================================================================
    // LOGIKA SHARED
    // =================================================================
    public function loadSharedStats()
    {
        // Cache total aset global karena dipakai admin & employee
        $this->totalAssets = Cache::remember('global_assets_count', 60, fn() => Asset::count());

        // Status aset global (Admin & Employee melihat data status yang sama)
        $this->assetsByStatus = AssetStatus::withCount('assets')
            ->get()
            ->map(fn ($s) => ['status' => $s, 'count' => $s->assets_count])
            ->toArray();
    }

    // =================================================================
    // LOGIKA ADMIN
    // =================================================================
    public function loadAdminStats()
    {
        $this->totalUsers = User::count();
        $this->totalMaintenances = Maintenance::count();

        $this->totalMaintenancesThisMonth = Maintenance::whereMonth('execution_date', now()->month)
            ->whereYear('execution_date', now()->year)
            ->count();
    }

    public function loadAdminHistory()
    {
        $this->generateHistoryData(Maintenance::query());
    }

    public function loadAdminChart()
    {
        $stats = Maintenance::select('maintenance_type', DB::raw('count(*) as total'))
            ->groupBy('maintenance_type')
            ->get();
        
        $this->formatChartData($stats);
    }

    public function updatingSearchLocation()
    {
        $this->resetPage();
    }

    public function resetSearch()
    {
        $this->searchLocation = '';
        $this->resetPage();
    }

    // Helper rekursif admin
    private function getTotalAssetsWithChildren($location)
    {
        $count = $location->assets->count();
        if ($location->children) {
            foreach ($location->children as $child) {
                $count += $this->getTotalAssetsWithChildren($child);
            }
        }
        return $count;
    }

    // =================================================================
    // LOGIKA EMPLOYEE
    // =================================================================
    public function loadEmployeeStats($userId)
    {
        // Query Scope: maintenance milik user ini
        $myQuery = Maintenance::whereHas('technicians', fn($q) => $q->where('users.id', $userId));
        
        $this->myTotalMaintenances = (clone $myQuery)->count();
        $this->myMonthMaintenances = (clone $myQuery)
            ->whereMonth('execution_date', now()->month)
            ->whereYear('execution_date', now()->year)
            ->count();
    }

    public function loadEmployeeHistory($userId)
    {
        $query = Maintenance::whereHas('technicians', fn($q) => $q->where('users.id', $userId));
        $this->generateHistoryData($query);
    }

    public function loadEmployeeChart($userId)
    {
        $stats = Maintenance::whereHas('technicians', fn($q) => $q->where('users.id', $userId))
            ->select('maintenance_type', DB::raw('count(*) as total'))
            ->groupBy('maintenance_type')
            ->get();

        $this->formatChartData($stats);
    }

    // =================================================================
    // HELPER GENERATOR (DRY - Don't Repeat Yourself)
    // =================================================================
    private function generateHistoryData($queryBuilder)
    {
        $endDate = Carbon::today();
        $startDate = Carbon::today()->subDays(6);

        $historyData = $queryBuilder
            ->whereBetween('execution_date', [
                $startDate->format('Y-m-d 00:00:00'), 
                $endDate->format('Y-m-d 23:59:59')
            ])
            ->selectRaw('DATE(execution_date) as date, count(*) as count')
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

    private function formatChartData($stats)
    {
        $typeLabels = Maintenance::getTypes(); // Asumsi method ini ada di Model
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

    // Helper Rekursif untuk Mengambil Kategori & Jumlahnya
    private function getCategoryBreakdown($location)
    {
        $breakdown = [];

        // 1. Hitung aset di lokasi ini
        foreach ($location->assets as $asset) {
            if ($asset->model && $asset->model->category) {
                $catName = $asset->model->category->name;
                if (!isset($breakdown[$catName])) {
                    $breakdown[$catName] = 0;
                }
                $breakdown[$catName]++;
            }
        }

        // 2. Hitung aset di child locations (Rekursif)
        if ($location->children) {
            foreach ($location->children as $child) {
                $childBreakdown = $this->getCategoryBreakdown($child);
                foreach ($childBreakdown as $catName => $count) {
                    if (!isset($breakdown[$catName])) {
                        $breakdown[$catName] = 0;
                    }
                    $breakdown[$catName] += $count;
                }
            }
        }

        return $breakdown;
    }

    // =================================================================
    // RENDER
    // =================================================================
    public function render()
    {
        $isAdmin = Auth::user()->role === 'admin';
        $viewData = [];

        if ($isAdmin) {
            // QUERY TABLE ADMIN (Lokasi)
            $locations = Location::query()
                ->with(['parent', 'assets.model.category', 'children.assets.model.category']) 
                ->when($this->searchLocation, function ($query) {
                    $query->where('name', 'like', '%' . $this->searchLocation . '%')
                          ->orWhereHas('parent', fn($q) => $q->where('name', 'like', '%' . $this->searchLocation . '%'));
                })
                ->orderBy('name', 'asc')
                ->paginate(10);

            // Hitung total aset + children
            $locations->getCollection()->transform(function ($location) {
                $location->total_assets_with_children = $this->getTotalAssetsWithChildren($location);
                
                // PANGGIL HELPER BARU
                $location->category_breakdown = $this->getCategoryBreakdown($location);
                
                return $location;
            });

            $viewData['paginatedLocations'] = $locations;

        } else {
            // QUERY TABLE EMPLOYEE (Latest Maintenance)
            $latestMaintenances = Maintenance::with([
                    'asset.model', 
                    'asset.defaultLocation.parent'
                ])
                ->whereHas('technicians', fn($q) => $q->where('users.id', Auth::id()))
                ->latest('execution_date')
                ->take(10)
                ->get();

            $viewData['latestMaintenances'] = $latestMaintenances;
        }

        return view('livewire.dashboard', $viewData);
    }
}