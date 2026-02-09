<div class="container mx-auto px-4">
    
    {{-- HEADER: TAMPILAN SAMA UNTUK ADMIN & EMPLOYEE --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        
        {{-- BAGIAN KIRI: SAPAAN PERSONAL --}}
        <div class="text-center md:text-left"> 
            <h1 class="text-3xl font-bold text-gray-800">Halo, {{ auth()->user()->name }}!</h1>
            <p class="text-gray-500 text-sm mt-1">
                {{-- Teks berubah otomatis sesuai role --}}
                Selamat datang di Dashboard {{ auth()->user()->role === 'admin' ? 'Admin' : 'Karyawan' }}.
            </p>
        </div>

        {{-- BAGIAN KANAN: JAM DIGITAL (MUNCUL UNTUK SEMUA) --}}
        <div class="text-center md:text-right" 
             x-data="{ time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }) }"
             x-init="setInterval(() => time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }), 1000)">
            <p class="text-sm font-semibold text-gray-600">{{ now()->translatedFormat('l, d F Y') }}</p>
            <p class="text-sm text-gray-500 font-mono mt-0.5" x-text="time">
                {{-- Fallback PHP jika JS belum load --}}
                {{ now()->format('H:i:s') }}
            </p>
        </div>
    </div>

    {{-- SECTION 1: KARTU STATISTIK (ADAPTIF) --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        
        {{-- CARD 1: TOTAL ASET (SHARED) --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-600">Total Aset</h2>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalAssets }}</p>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'admin')
            {{-- CARD 2 & 3 ADMIN --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-semibold text-gray-600">Total Pengguna</h2>
                        <p class="text-2xl font-bold text-gray-800">{{ $totalUsers }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-semibold text-gray-600">Total Pemeliharaan</h2>
                        <p class="text-2xl font-bold text-gray-800">{{ $totalMaintenances }}</p>
                    </div>
                </div>
            </div>

            {{-- CARD 4 (BARU): PEMELIHARAAN BULAN INI --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-semibold text-gray-600">Pemeliharaan Bulan Ini</h2>
                        <p class="text-2xl font-bold text-gray-800">{{ $totalMaintenancesThisMonth }}</p>
                        <p class="text-xs text-gray-400">{{ now()->translatedFormat('F Y') }}</p>
                    </div>
                </div>
            </div>
        @else
            {{-- CARD 2 & 3 EMPLOYEE --}}
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-purple-100">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-semibold text-gray-600">Pekerjaan Saya</h2>
                        <p class="text-2xl font-bold text-gray-800">{{ $myTotalMaintenances }}</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-green-100">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-lg font-semibold text-gray-600">Bulan Ini</h2>
                        <p class="text-2xl font-bold text-gray-800">{{ $myMonthMaintenances }}</p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- SECTION 2: PROGRESS BAR (SHARED) --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Aset Berdasarkan Status</h2>
        @if(count($assetsByStatus) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach($assetsByStatus as $statusData)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow">
                    <div class="text-sm text-gray-600 font-medium truncate">
                        {{ $statusData['status']['name'] ?? 'Unknown' }}
                    </div>
                    <div class="text-2xl font-bold text-gray-800 mt-1">{{ $statusData['count'] }}</div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                        <div class="bg-blue-600 h-2 rounded-full"
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
            <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">Belum ada data status aset.</div>
        @endif
    </div>

    {{-- SECTION 3: GRAFIK BATANG RIWAYAT (SHARED STRUCTURE, DIFFERENT DATA) --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">
            {{ auth()->user()->role === 'admin' ? 'Riwayat Pemeliharaan Global' : 'Riwayat Pekerjaan Saya' }} (7 Hari Terakhir)
        </h2>
        <div class="overflow-x-auto">
            <div class="flex items-end space-x-4 h-72 pt-12 border-b border-l border-gray-200 pl-4 pb-4 pr-4 min-w-[600px]">
                @php $maxCount = $completeHistory->max('count') ?: 1; @endphp
                @foreach($completeHistory as $record)
                    <div class="flex flex-col items-center flex-1 group relative">
                        <div class="opacity-0 group-hover:opacity-100 absolute -top-10 bg-gray-800 text-white text-xs rounded py-1 px-2 transition-opacity z-10 whitespace-nowrap">
                            {{ $record['count'] }} Tiket
                        </div>
                        <div class="w-full bg-blue-500 rounded-t hover:bg-blue-600 transition-all duration-500 flex items-start justify-center pt-1 text-white text-xs font-bold shadow-sm"
                             style="height: {{ max(30, min(200, ($record['count'] / $maxCount) * 200)) }}px;">
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

    {{-- SECTION 4: GRID UTAMA (KIRI: TABEL, KANAN: CHART) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        
        {{-- KOLOM KIRI --}}
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6 h-full flex flex-col">
            
            @if(auth()->user()->role === 'admin')
                {{-- TABEL ADMIN (LOKASI) --}}
                <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Statistik Lokasi</h2>
                        <p class="text-sm text-gray-500">Monitoring aset per gedung atau ruangan.</p>
                    </div>
                    <div class="relative w-full md:w-56">
                        <input type="text" wire:model.live.debounce.300ms="searchLocation" placeholder="Cari gedung atau ruangan" class="w-full pl-3 pr-3 py-2 border border-gray-300 rounded-lg text-sm">
                        @if($searchLocation)
                            <button wire:click="resetSearch" class="absolute right-2 top-2 text-gray-400 hover:text-gray-600">✕</button>
                        @endif
                    </div>
                </div>

                @if($paginatedLocations->count() > 0)
                    <div class="overflow-x-auto flex-grow border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Lokasi</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase">Total</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Kategori</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($paginatedLocations as $loc)
                                <tr>
                                    <td class="px-4 py-3 align-top text-sm">
                                        <div class="font-bold text-gray-800">{{ $loc->name }}</div>
                                        @if($loc->parent) <div class="text-xs text-gray-500">↳ {{ $loc->parent->name }}</div> @endif
                                    </td>
                                    <td class="px-4 py-3 text-center align-top">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700">{{ $loc->total_assets_with_children }}</span>
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        @if(count($loc->category_breakdown) > 0)
                                            <div class="flex flex-wrap gap-1.5">
                                                {{-- Loop Breakdown Kategori --}}
                                                @foreach($loc->category_breakdown as $catName => $count)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 border border-gray-200">
                                                        {{ $catName }}: <span class="ml-1 font-bold">{{ $count }}</span>
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">Tidak ada aset</span>
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
                    <div class="text-center py-8 text-gray-500">Lokasi tidak ditemukan.</div>
                @endif

            @else
                {{-- TABEL EMPLOYEE (LATEST JOBS) --}}
                <div class="mb-6">
                    <h2 class="text-xl font-bold text-gray-800">10 Pekerjaan Terakhir</h2>
                    <p class="text-sm text-gray-500">Aktivitas terbaru Anda.</p>
                </div>

                @if($latestMaintenances->count() > 0)
                    <div class="overflow-x-auto flex-grow border border-gray-200 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Judul</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Aset / Lokasi</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tipe</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($latestMaintenances as $mt)
                                <tr class="hover:bg-blue-50 transition-colors">
                                    <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">{{ $mt->execution_date->format('d M Y') }}</td>
                                    <td class="px-4 py-3 text-sm font-bold text-gray-800">{{ $mt->title }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="font-bold text-gray-600">{{ $mt->asset->model->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $mt->asset->defaultLocation->name ?? '-' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $mt->badge_class }}">
                                            {{ $mt->type_label }}
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">Belum ada data pekerjaan.</div>
                @endif
            @endif
        </div>

        {{-- KOLOM KANAN: PIE CHART (SHARED) --}}
        <div class="lg:col-span-1 bg-white rounded-lg shadow-md p-6 h-fit sticky top-6">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Jenis Pemeliharaan</h2>
            <p class="text-sm text-gray-500 mb-6">Proporsi aktivitas.</p>

            @if(array_sum($maintenanceChartData['series']) > 0)
                <div wire:ignore x-data="maintenanceChart(@js($maintenanceChartData))" class="relative">
                    <div x-ref="chartContainer"></div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center h-64 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <span class="text-sm text-gray-400">Belum ada data</span>
                </div>
            @endif
        </div>
    </div>

    {{-- SECTION 5: QUICK ACTIONS (ADAPTIF) --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Aksi Cepat</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            @if(auth()->user()->role === 'admin')
                {{-- TOMBOL ADMIN --}}
                <a href="{{ route('assets.create') }}" wire:navigate class="group bg-white border border-gray-200 rounded-xl p-5 flex items-center hover:shadow-lg hover:border-blue-500 transition-all">
                    <div class="p-3 bg-blue-50 rounded-lg text-blue-600 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                    </div>
                    <div class="ml-4 font-bold text-gray-800 group-hover:text-blue-600">Tambah Aset</div>
                </a>
                
                <a href="{{ route('users') }}" wire:navigate class="group bg-white border border-gray-200 rounded-xl p-5 flex items-center hover:shadow-lg hover:border-green-500 transition-all">
                    <div class="p-3 bg-green-50 rounded-lg text-green-600 group-hover:bg-green-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                    </div>
                    <div class="ml-4 font-bold text-gray-800 group-hover:text-green-600">Kelola User</div>
                </a>
            @endif

            {{-- TOMBOL SHARED (MAINTENANCE) --}}
            <a href="{{route('maintenances.create')}}" wire:navigate class="group bg-white border border-gray-200 rounded-xl p-5 flex items-center hover:shadow-lg hover:border-yellow-500 transition-all">
                <div class="p-3 bg-yellow-50 rounded-lg text-yellow-600 group-hover:bg-yellow-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                </div>
                <div class="ml-4 font-bold text-gray-800 group-hover:text-yellow-600">Tambah Pemeliharaan</div>
            </a>
        </div>
    </div>
</div>

{{-- SCRIPT: HANYA PERLU SEKALI --}}
@assets
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endassets

@script
<script>
    Alpine.data('maintenanceChart', (initialData) => ({
        chart: null,
        data: initialData,
        init() {
            let options = {
                series: this.data.series,
                labels: this.data.labels,
                chart: { type: 'pie', height: 350, fontFamily: 'inherit', toolbar: { show: false } },
                colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
                dataLabels: { enabled: true, style: { fontSize: '14px', fontWeight: 'bold' }, dropShadow: { enabled: false } },
                legend: { position: 'bottom', fontSize: '12px' },
                responsive: [{ breakpoint: 480, options: { chart: { height: 300 } } }]
            };
            this.chart = new ApexCharts(this.$refs.chartContainer, options);
            this.chart.render();
        }
    }));
</script>
@endscript