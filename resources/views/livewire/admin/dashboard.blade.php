<div class="container mx-auto px-4">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard Admin</h1>

    {{-- 1. KARTU STATISTIK ATAS --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        {{-- Total Aset --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-600">Total Aset</h2>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalAssets }}</p>
                </div>
            </div>
        </div>

        {{-- Total Pengguna --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-600">Total Pengguna</h2>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalUsers }}</p>
                </div>
            </div>
        </div>

        {{-- Total Maintenance --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-600">Pemeliharaan</h2>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalMaintenances }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. PROGRESS BAR STATUS --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Aset Berdasarkan Status</h2>
        @if(count($assetsByStatus) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach($assetsByStatus as $statusData)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow">
                    <div class="text-sm text-gray-600 font-medium truncate">
                        {{ $statusData['status']['name'] ?? 'Status Tidak Diketahui' }}
                    </div>
                    <div class="text-2xl font-bold text-gray-800 mt-1">{{ $statusData['count'] }}</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                             style="width: {{ $totalAssets > 0 ? ($statusData['count'] / $totalAssets) * 100 : 0 }}%">
                        </div>
                    </div>
                    <div class="text-xs text-gray-400 mt-1 text-right">
                        {{ $totalAssets > 0 ? round(($statusData['count'] / $totalAssets) * 100) : 0 }}%
                    </div>
                </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                Belum ada data status aset.
            </div>
        @endif
    </div>

    {{-- 3. BAR CHART MANUAL (CSS ONLY) --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Riwayat Pemeliharaan (7 Hari Terakhir)</h2>
        <div class="overflow-x-auto">
            <div class="flex items-end space-x-4 h-72 pt-12 border-b border-l border-gray-200 pl-4 pb-4 pr-4 min-w-[600px]">
                @php
                    $maxCount = $completeHistory->max('count') ?: 1;
                @endphp
                @foreach($completeHistory as $record)
                    <div class="flex flex-col items-center flex-1 group relative">
                        <div class="opacity-0 group-hover:opacity-100 absolute -top-10 bg-gray-800 text-white text-xs rounded py-1 px-2 transition-opacity z-10 whitespace-nowrap">
                            {{ $record['count'] }} Maintenance
                        </div>
                        <div
                            class="w-full bg-blue-500 rounded-t hover:bg-blue-600 transition-all duration-500 flex items-start justify-center pt-1 text-white text-xs font-bold shadow-sm group-hover:shadow-md"
                            style="height: {{ max(30, min(200, ($record['count'] / $maxCount) * 200)) }}px;"
                        >
                            @if($record['count'] > 0) {{ $record['count'] }} @endif
                        </div>
                        <div class="text-xs text-gray-500 font-medium mt-3 text-center">
                            {{ \Carbon\Carbon::parse($record['date'])->translatedFormat('d M') }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- 4. GRID UTAMA (TABEL + PIE CHART) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- KOLOM KIRI: TABEL LOKASI --}}
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6 h-full flex flex-col">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Statistik Aset Per Lokasi</h2>
                    <p class="text-sm text-gray-500 mt-1">Monitoring aset di setiap gedung & ruangan.</p>
                </div>
                <div class="flex gap-2 w-full md:w-auto">
                    <div class="relative w-full md:w-56">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="searchLocation"
                            placeholder="Cari lokasi..."
                            class="w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm"
                        >
                    </div>
                    @if($searchLocation)
                    <button wire:click="resetSearch" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-sm transition-colors" title="Reset">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                    @endif
                </div>
            </div>

            @if($paginatedLocations->count() > 0)
                <div class="overflow-x-auto flex-grow border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/3">Lokasi</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-16">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Sebaran Kategori</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($paginatedLocations as $location)
                            @php
                                // Ambil total aset termasuk children
                                $totalAssets = $location->total_assets_with_children;
                                
                                // Kumpulkan semua aset dari lokasi ini + children
                                $allAssets = collect($location->assets);
                                foreach ($location->children as $child) {
                                    $allAssets = $allAssets->merge($child->assets);
                                }
                            @endphp
                            <tr class="hover:bg-blue-50 transition-colors group">
                                <td class="px-4 py-3 align-top">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mr-2 mt-0.5">
                                            @if($location->parent)
                                                <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 100-4 2 2 0 000 4zM8 14v1m4 3v.01M16 17v.01M16 11V8.5l-8.5 8.5" /></svg>
                                            @else
                                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 100-4 2 2 0 000 4z" /></svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-gray-800">{{ $location->name }}</div>
                                            @if($location->parent)
                                                <div class="text-xs text-gray-500 flex items-center">
                                                    <span class="mr-1">↳</span> {{ $location->parent->name }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center align-top">
                                    @if($totalAssets > 0)
                                        <span class="inline-flex items-center justify-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                            {{ $totalAssets }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 align-top">
                                    @if($totalAssets > 0)
                                        <div class="flex flex-wrap gap-1.5">
                                            @foreach($allAssets->groupBy(fn($a) => $a->model->category->name ?? 'Lainnya') as $categoryName => $assets)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded border border-gray-200 bg-gray-50 text-[10px] text-gray-600">
                                                    {{ Str::limit($categoryName, 12) }}
                                                    <span class="ml-1 font-bold text-gray-900">{{ $assets->count() }}</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Kosong</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 pt-2 border-t border-gray-100">
                    {{ $paginatedLocations->links(data: ['scrollTo' => false]) }}
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-64 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                    <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 100-4 2 2 0 000 4z"></path></svg>
                    <p class="text-gray-500 text-sm">Lokasi tidak ditemukan.</p>
                </div>
            @endif
        </div>

        {{-- KOLOM KANAN: PIE CHART --}}
        <div class="lg:col-span-1 bg-white rounded-lg shadow-md p-6 h-fit sticky top-6">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Jenis Pemeliharaan</h2>
            <p class="text-sm text-gray-500 mb-6">Proporsi aktivitas pemeliharaan.</p>

            @if(array_sum($maintenanceChartData['series']) > 0)
                {{-- AREA CHART DENGAN ALPINE --}}
                <div
                    wire:ignore
                    x-data="maintenanceChart(@js($maintenanceChartData))"
                    class="relative"
                >
                    {{-- Element ID tempat ApexCharts merender --}}
                    <div x-ref="chartContainer"></div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-gray-600 border-t pt-4">
                    <div class="col-span-2 text-center font-medium mb-1">
                        Total Data: {{ array_sum($maintenanceChartData['series']) }}
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-64 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
                    </svg>
                    <span class="text-sm text-gray-400">Belum ada data pemeliharaan</span>
                </div>
            @endif
        </div>
    </div>

    {{-- 5. AKSI CEPAT (QUICK ACTIONS) --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Aksi Cepat</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            {{-- Tambah Aset --}}
            <a href="{{ route('admin.assets.create') }}" wire:navigate class="group relative bg-white border border-gray-200 rounded-xl p-5 flex items-center hover:shadow-lg hover:border-blue-500 transition-all duration-300">
                <div class="flex-shrink-0 p-3 bg-blue-50 rounded-lg group-hover:bg-blue-600 transition-colors duration-300">
                    <svg class="w-6 h-6 text-blue-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <div class="ml-4 flex-grow">
                    <h3 class="text-lg font-bold text-gray-800 group-hover:text-blue-600 transition-colors">Tambah Aset</h3>
                </div>
                <div class="absolute right-4 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-300 text-blue-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </a>

            {{-- Tambah Pengguna --}}
            <a href="{{ route('admin.users') }}" wire:navigate class="group relative bg-white border border-gray-200 rounded-xl p-5 flex items-center hover:shadow-lg hover:border-green-500 transition-all duration-300">
                <div class="flex-shrink-0 p-3 bg-green-50 rounded-lg group-hover:bg-green-600 transition-colors duration-300">
                    <svg class="w-6 h-6 text-green-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
                <div class="ml-4 flex-grow">
                    <h3 class="text-lg font-bold text-gray-800 group-hover:text-green-600 transition-colors">Tambah Pengguna</h3>
                </div>
                <div class="absolute right-4 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-300 text-green-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </a>

            {{-- Tambah Pemeliharaan --}}
            <a href="{{ route('admin.maintenances.create') }}" wire:navigate class="group relative bg-white border border-gray-200 rounded-xl p-5 flex items-center hover:shadow-lg hover:border-yellow-500 transition-all duration-300">
                <div class="flex-shrink-0 p-3 bg-yellow-50 rounded-lg group-hover:bg-yellow-600 transition-colors duration-300">
                    <svg class="w-6 h-6 text-yellow-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div class="ml-4 flex-grow">
                    {{-- Saya ubah teksnya jadi Tambah Pemeliharaan sesuai request --}}
                    <h3 class="text-lg font-bold text-gray-800 group-hover:text-yellow-600 transition-colors">Tambah Pemeliharaan</h3>
                </div>
                <div class="absolute right-4 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-300 text-yellow-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </a>

        </div>
    </div>
</div>

{{-- SCRIPT PENTING --}}
@assets
    {{-- Memuat ApexCharts dari CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endassets

@script
<script>
    Alpine.data('maintenanceChart', (initialData) => ({
        chart: null,
        data: initialData,

        init() {
            let options = {
                // 1. Data & Label
                series: this.data.series,
                labels: this.data.labels,

                // 2. Konfigurasi Dasar
                chart: {
                    type: 'pie',
                    height: 350,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    animations: { enabled: true }
                },

                // 3. Warna (Tailwind Palette)
                colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#6366F1'],
                
                // 4. Konfigurasi Tampilan Angka di Dalam Chart (Data Labels)
                dataLabels: { 
                    enabled: true,
                    formatter: function (val, opts) {
                        return opts.w.config.series[opts.seriesIndex]
                    },
                    style: {
                        fontSize: '14px',
                        fontFamily: 'inherit',
                        fontWeight: 'bold',
                        colors: ['#fff']
                    },
                    dropShadow: { enabled: false }
                },

                // 5. Konfigurasi Legend
                legend: {
                    show: true,
                    position: 'bottom',
                    horizontalAlign: 'center',
                    floating: false,
                    fontSize: '12px',
                    fontFamily: 'inherit',
                    fontWeight: 400,
                    inverseOrder: false,
                    itemMargin: {
                        horizontal: 10,
                        vertical: 5
                    },
                    formatter: function(seriesName, opts) {
                        return seriesName
                    }
                },

                // 6. Tooltip
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: function (val) {
                            return val + " Tiket"
                        }
                    }
                },

                // 7. Responsif
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 300
                        },
                        legend: {
                            position: 'bottom',
                            offsetX: 0,
                            offsetY: 0
                        }
                    }
                }]
            };

            this.chart = new ApexCharts(this.$refs.chartContainer, options);
            this.chart.render();
        }
    }));
</script>
@endscript