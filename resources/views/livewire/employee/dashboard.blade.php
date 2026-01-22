<div class="container mx-auto px-4">
    {{-- 
        SECTION: HEADER DASHBOARD
        Menampilkan sapaan pengguna dan jam digital real-time.
    --}}
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
        {{-- Sapaan Pengguna --}}
        <div class="text-center md:text-left"> 
            <h1 class="text-3xl font-bold text-gray-800">Halo, {{ auth()->user()->name }}!</h1>
            <p class="text-gray-500 text-sm mt-1">Selamat datang di Dashboard Karyawan.</p>
        </div>

        {{-- 
            Komponen Jam Digital (Alpine.js)
            Logic: 
            1. Menginisialisasi waktu saat ini pada properti 'time'.
            2. 'init()' menjalankan interval setiap 1 detik untuk memperbarui properti 'time'.
        --}}
        <div class="text-center md:text-right" 
            x-data="{ 
                time: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false }),
                init() {
                    setInterval(() => {
                        this.time = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
                    }, 1000);
                }
            }">
            {{-- Menampilkan Tanggal Statis --}}
            <p class="text-sm font-semibold text-gray-600">
                {{ now()->translatedFormat('l, d F Y') }}
            </p>
            
            {{-- Menampilkan Jam Dinamis (Reactive) --}}
            <p class="text-sm text-gray-500 font-mono mt-0.5" x-text="time">
                {{ now()->format('H:i:s') }}
            </p>
        </div>
    </div>

    {{-- 
        SECTION: KARTU STATISTIK UTAMA
        Menampilkan ringkasan data numerik (Total Aset, Pekerjaan Saya, Kinerja Bulan Ini).
    --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        {{-- Card 1: Total Aset --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-600">Total Aset</h2>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalAssets }}</p>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Pekerjaan Saya --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-600">Pekerjaan Saya</h2>
                    <p class="text-2xl font-bold text-gray-800">{{ $myTotalMaintenances }}</p>
                </div>
            </div>
        </div>

        {{-- Card 3: Kinerja Bulan Ini --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <h2 class="text-lg font-semibold text-gray-600">Bulan Ini</h2>
                    <p class="text-2xl font-bold text-gray-800">{{ $myMonthMaintenances }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- 
        SECTION: PROGRESS BAR STATUS ASET
        Visualisasi distribusi aset berdasarkan statusnya.
    --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Aset Berdasarkan Status</h2>
        
        {{-- Logic: Cek apakah array $assetsByStatus memiliki data --}}
        @if(count($assetsByStatus) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                @foreach($assetsByStatus as $statusData)
                <div class="border border-gray-200 rounded-lg p-4 hover:shadow-sm transition-shadow">
                    <div class="text-sm text-gray-600 font-medium truncate">
                        {{ $statusData['status']['name'] ?? 'Status Tidak Diketahui' }}
                    </div>
                    <div class="text-2xl font-bold text-gray-800 mt-1">{{ $statusData['count'] }}</div>
                    
                    {{-- Visualisasi Bar --}}
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-3">
                        {{-- 
                            Logic Kalkulasi Lebar (%):
                            (Jumlah per status / Total Aset) * 100.
                            Dilengkapi pengecekan ($totalAssets > 0) untuk menghindari error pembagian dengan nol.
                        --}}
                        <div class="bg-blue-600 h-2 rounded-full transition-all duration-500"
                             style="width: {{ $totalAssets > 0 ? ($statusData['count'] / $totalAssets) * 100 : 0 }}%">
                        </div>
                    </div>
                    
                    <div class="text-xs text-gray-400 mt-1 text-right">
                        {{-- Menampilkan persentase dalam angka --}}
                        {{ $totalAssets > 0 ? round(($statusData['count'] / $totalAssets) * 100) : 0 }}%
                    </div>
                </div>
                @endforeach
            </div>
        @else
            {{-- State Kosong: Ditampilkan jika tidak ada data --}}
            <div class="text-center py-8 text-gray-500 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                Belum ada data status aset.
            </div>
        @endif
    </div>

    {{-- 
        SECTION: HISTOGRAM RIWAYAT PEKERJAAN
        Grafik batang sederhana menggunakan HTML/CSS tanpa library charting eksternal.
    --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Riwayat Pekerjaan Saya (7 Hari Terakhir)</h2>
        <div class="overflow-x-auto">
            <div class="flex items-end space-x-4 h-72 pt-12 border-b border-l border-gray-200 pl-4 pb-4 pr-4 min-w-[600px]">
                {{-- Logic: Mencari nilai tertinggi (max) untuk skala sumbu Y grafik --}}
                @php
                    $maxCount = $completeHistory->max('count') ?: 1;
                @endphp
                
                @foreach($completeHistory as $record)
                    <div class="flex flex-col items-center flex-1 group relative">
                        {{-- Tooltip Hover --}}
                        <div class="opacity-0 group-hover:opacity-100 absolute -top-10 bg-gray-800 text-white text-xs rounded py-1 px-2 transition-opacity z-10 whitespace-nowrap">
                            {{ $record['count'] }} Tiket
                        </div>
                        
                        {{-- 
                            Logic Tinggi Batang:
                            Rumus: (Jumlah / Max) * 200px.
                            Fungsi max(30, ...) memastikan batang tetap terlihat minimal 30px meskipun datanya sedikit.
                        --}}
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

    {{-- 
        SECTION: GRID UTAMA
        Berisi Tabel Riwayat Terakhir (Kiri) dan Pie Chart Jenis Pekerjaan (Kanan).
    --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        {{-- KOLOM KIRI: TABEL 10 RIWAYAT TERAKHIR --}}
        <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6 h-full flex flex-col">
            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">10 Pekerjaan Terakhir</h2>
                    <p class="text-sm text-gray-500 mt-1">Daftar aktivitas yang baru Anda selesaikan.</p>
                </div>
            </div>

            @if($latestMaintenances->count() > 0)
                <div class="overflow-x-auto flex-grow border border-gray-200 rounded-lg">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-1/4">Judul</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aset</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lokasi</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jenis pemeliharaan</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($latestMaintenances as $mt)
                            <tr class="hover:bg-blue-50 transition-colors group">
                                
                                {{-- Tanggal --}}
                                <td class="px-4 py-3 align-top text-sm text-gray-600 whitespace-nowrap">
                                    {{ $mt->execution_date->format('d M Y') }}
                                </td>

                                {{-- Judul --}}
                                <td class="px-4 py-3 align-top">
                                    <div class="text-sm font-bold text-gray-800">{{ $mt->title }}</div>                              
                                </td>

                                {{-- Informasi Aset (Nama Model & Tag) --}}
                                <td class="px-4 py-3 align-top">
                                    <div class="text-sm font-bold text-gray-600">
                                        {{ $mt->asset->model->name ?? 'Model Tidak Diketahui' }}
                                    </div>
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        Tag: <span class="font-mono text-gray-700">{{ $mt->asset->asset_tag ?? '-' }}</span>
                                    </div>
                                </td>

                                {{-- Informasi Lokasi (Gedung & Ruangan) --}}
                                <td class="px-4 py-3 align-top">
                                    {{-- Logic: Pastikan relasi aset dan lokasi tersedia sebelum render --}}
                                    @if($mt->asset && $mt->asset->defaultLocation)
                                        <div class="flex items-start">
                                            {{-- Icon Kiri --}}
                                            <div class="flex-shrink-0 mr-2 mt-0.5">
                                                @if($mt->asset->defaultLocation->parent)
                                                    {{-- Icon Gedung (Jika ada parent) - Warna Biru --}}
                                                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 100-4 2 2 0 000 4zM8 14v1m4 3v.01M16 17v.01M16 11V8.5l-8.5 8.5" />
                                                    </svg>
                                                @else
                                                    {{-- Icon Standar (Jika tidak ada parent) - Warna Ungu --}}
                                                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 100-4 2 2 0 000 4z" />
                                                    </svg>
                                                @endif
                                            </div>

                                            {{-- Teks Lokasi --}}
                                            <div>
                                                {{-- Nama Lokasi Utama --}}
                                                <div class="text-sm font-bold text-gray-800">
                                                    {{ $mt->asset->defaultLocation->name }}
                                                </div>
                                                
                                                {{-- Nama Parent (Gedung) dengan panah bawah --}}
                                                @if($mt->asset->defaultLocation->parent)
                                                    <div class="text-xs text-gray-500 flex items-center">
                                                        <span class="mr-1">↳</span> 
                                                        {{ $mt->asset->defaultLocation->parent->name }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        {{-- Fallback jika data kosong --}}
                                        <span class="text-xs text-gray-400 italic">-</span>
                                    @endif
                                </td>

                                {{-- Badge Jenis Pekerjaan --}}
                                <td class="px-4 py-3 align-top">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $mt->badge_class }}">
                                        {{ $mt->type_label }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                {{-- State Kosong untuk Tabel --}}
                <div class="flex flex-col items-center justify-center h-64 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                    <svg class="w-10 h-10 text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m8-2a2 2 0 100-4 2 2 0 000 4z"></path></svg>
                    <p class="text-gray-500 text-sm">Belum ada data pemeliharaan.</p>
                </div>
            @endif
        </div>

        {{-- KOLOM KANAN: PIE CHART (ApexCharts) --}}
        <div class="lg:col-span-1 bg-white rounded-lg shadow-md p-6 h-fit sticky top-6">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Jenis Pekerjaan</h2>
            <p class="text-sm text-gray-500 mb-6">Proporsi tipe pemeliharaan Anda.</p>

            @if(array_sum($maintenanceChartData['series']) > 0)
                {{-- Container Chart dengan Alpine.js initialization --}}
                <div
                    wire:ignore
                    x-data="maintenanceChart(@js($maintenanceChartData))"
                    class="relative"
                >
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
                    <span class="text-sm text-gray-400">Belum ada data</span>
                </div>
            @endif
        </div>
    </div>

    {{-- 
        SECTION: TOMBOL AKSI CEPAT
        Tombol navigasi untuk membuat pemeliharaan baru.
    --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-8">
        <h2 class="text-xl font-bold text-gray-800 mb-6">Aksi Cepat</h2>
        
        <div class="grid grid-cols-1">
            
            <a href="#" wire:navigate class="group relative bg-white border border-gray-200 rounded-xl p-5 flex items-center hover:shadow-lg hover:border-yellow-500 transition-all duration-300">
                <div class="flex-shrink-0 p-3 bg-yellow-50 rounded-lg group-hover:bg-yellow-600 transition-colors duration-300">
                    <svg class="w-6 h-6 text-yellow-600 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div class="ml-4 flex-grow">
                    <h3 class="text-lg font-bold text-gray-800 group-hover:text-yellow-600 transition-colors">Tambah Pemeliharaan</h3>
                    <p class="text-sm text-gray-500 mt-1">Catat aktivitas pemeliharaan atau perbaikan baru.</p>
                </div>
                <div class="absolute right-4 top-1/2 transform -translate-y-1/2 opacity-0 group-hover:opacity-100 group-hover:translate-x-1 transition-all duration-300 text-yellow-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </a>

        </div>
    </div>
</div>

@assets
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
@endassets

@script
<script>
    /**
     * Komponen Alpine.js untuk merender Pie Chart menggunakan library ApexCharts.
     * * @param {Object} initialData - Objek berisi array 'labels' dan 'series' dari PHP.
     */
    Alpine.data('maintenanceChart', (initialData) => ({
        chart: null,
        data: initialData,

        /**
         * Lifecycle Init: Dipanggil saat komponen Alpine diinisialisasi.
         * Bertugas mengkonfigurasi dan merender chart ke elemen DOM terkait.
         */
        init() {
            let options = {
                series: this.data.series,
                labels: this.data.labels,
                chart: {
                    type: 'pie',
                    height: 350,
                    fontFamily: 'inherit',
                    toolbar: { show: false },
                    animations: { enabled: true }
                },
                colors: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#EC4899', '#6366F1'],
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
                legend: {
                    show: true,
                    position: 'bottom',
                    horizontalAlign: 'center',
                    floating: false,
                    fontSize: '12px',
                    fontFamily: 'inherit',
                    fontWeight: 400,
                    itemMargin: { horizontal: 10, vertical: 5 },
                    formatter: function(seriesName, opts) { return seriesName }
                },
                tooltip: {
                    theme: 'light',
                    y: {
                        formatter: function (val) { return val + " Tiket" }
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: { height: 300 },
                        legend: { position: 'bottom' }
                    }
                }]
            };

            this.chart = new ApexCharts(this.$refs.chartContainer, options);
            this.chart.render();
        }
    }));
</script>
@endscript