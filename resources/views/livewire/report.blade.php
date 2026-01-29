<div class="container mx-auto px-4 max-w-5xl">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-extrabold text-slate-800">
            {{ $isAdmin ? 'Ekspor Laporan' : 'Ekspor Laporan Saya' }}
        </h1>
        <p class="text-slate-500 text-sm mt-1">
            {{ $isAdmin ? 'Unduh data pemeliharaan atau aset dalam format yang Anda butuhkan' : 'Unduh riwayat pekerjaan pemeliharaan Anda' }}
        </p>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('error'))
        <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-3 rounded-r-lg flex items-start gap-2">
            <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span class="text-red-700 text-sm font-medium">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Main Card --}}
    <div class="bg-white shadow-lg rounded-2xl overflow-hidden border border-slate-100">
        <div class="p-6 space-y-6">

            {{-- Step 1: Jenis Laporan (HANYA UNTUK ADMIN) --}}
            @if($isAdmin)
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-3">
                        <span class="inline-flex items-center gap-2">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full {{ $selectedDataType === 'aset' ? 'bg-green-100 text-green-600' : 'bg-blue-100 text-blue-600' }} text-xs font-bold">1</span>
                            Pilih Jenis Laporan
                        </span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            wire:click="$set('selectedDataType', 'pemeliharaan')"
                            class="group relative flex flex-col items-center p-4 rounded-xl border-2 transition-all {{ $selectedDataType === 'pemeliharaan' ? 'border-blue-500 bg-blue-50/50' : 'border-slate-200 hover:border-blue-300' }}"
                        >
                            <div class="p-2 rounded-lg mb-2 {{ $selectedDataType === 'pemeliharaan' ? 'bg-blue-100 text-blue-600' : 'bg-slate-100 text-slate-400' }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <span class="font-semibold text-sm text-slate-700">Pemeliharaan</span>
                            @if($selectedDataType === 'pemeliharaan')
                                <div class="absolute -top-1 -right-1 text-blue-500">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                </div>
                            @endif
                        </button>

                        <button
                            wire:click="$set('selectedDataType', 'aset')"
                            class="group relative flex flex-col items-center p-4 rounded-xl border-2 transition-all {{ $selectedDataType === 'aset' ? 'border-green-500 bg-green-50/50' : 'border-slate-200 hover:border-green-300' }}"
                        >
                            <div class="p-2 rounded-lg mb-2 {{ $selectedDataType === 'aset' ? 'bg-green-100 text-green-600' : 'bg-slate-100 text-slate-400' }}">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                            </div>
                            <span class="font-semibold text-sm text-slate-700">Aset</span>
                            @if($selectedDataType === 'aset')
                                <div class="absolute -top-1 -right-1 text-green-500">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                </div>
                            @endif
                        </button>
                    </div>
                </div>
            @endif

            {{-- Step 2 (Admin) / Step 1 (Employee): Filter Data --}}
            @if ($selectedDataType)
                <div class="animate-fade-in">
                    <label class="block text-sm font-bold text-slate-700 mb-3">
                        <span class="inline-flex items-center gap-2">
                            {{-- Nomor langkah dinamis: Jika Admin, ini langkah 2. Jika Employee, ini langkah 1 --}}
                            <span class="flex items-center justify-center w-6 h-6 rounded-full {{ $selectedDataType === 'pemeliharaan' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600' }} text-xs font-bold">
                                {{ $isAdmin ? 2 : 1 }}
                            </span>
                            @if ($selectedDataType === 'pemeliharaan')
                                Pilih Bulan <span class="text-red-500">*</span>
                            @else
                                Pilih Kategori Aset <span class="text-red-500">*</span>
                            @endif
                        </span>
                    </label>
                    
                    <div wire:loading.class="opacity-50 pointer-events-none" wire:target="selectedDataType, selectedMonth, selectedCategory">
                        @if ($selectedDataType === 'pemeliharaan')
                            <select
                                wire:model.live="selectedMonth"
                                class="block w-full px-4 py-3 text-sm border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-xl bg-white shadow-sm"
                            >
                                <option value="">-- Pilih Bulan --</option>
                                @foreach ($months as $month)
                                    <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                                @endforeach
                            </select>
                        @else
                            <select
                                wire:model.live="selectedCategory"
                                class="block w-full px-4 py-3 text-sm border-slate-300 focus:ring-2 focus:ring-green-500 focus:border-green-500 rounded-xl bg-white shadow-sm"
                            >
                                <option value="">-- Pilih Kategori --</option>
                                @foreach ($assetCategories as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        @endif
                    </div>
                </div>

                {{-- Step 3 (Admin) / Step 2 (Employee): Format Ekspor --}}
                <div class="animate-fade-in">
                    <label class="block text-sm font-bold text-slate-700 mb-3">
                        <span class="inline-flex items-center gap-2">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full {{ $selectedDataType === 'pemeliharaan' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600' }} text-xs font-bold">
                                {{ $isAdmin ? 3 : 2 }}
                            </span>
                            Format Ekspor
                        </span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            wire:click="$set('selectedFormat', 'pdf')"
                            class="flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-semibold border-2 transition-all {{ $selectedFormat === 'pdf' ? 'bg-red-600 border-red-600 text-white shadow-md' : 'bg-white border-slate-200 text-slate-600 hover:border-red-300' }}"
                        >
                            <svg class="w-5 h-5 {{ $selectedFormat === 'pdf' ? 'text-white' : 'text-red-500' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                            PDF
                        </button>

                        <button
                            wire:click="$set('selectedFormat', 'excel')"
                            class="flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-semibold border-2 transition-all {{ $selectedFormat === 'excel' ? 'bg-green-600 border-green-600 text-white shadow-md' : 'bg-white border-slate-200 text-slate-600 hover:border-green-300' }}"
                        >
                            <svg class="w-5 h-5 {{ $selectedFormat === 'excel' ? 'text-white' : 'text-green-600' }}" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 4a3 3 0 00-3 3v6a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H5zm-1 9v-1h5v2H5a1 1 0 01-1-1zm7 1h4a1 1 0 001-1v-1h-5v2zm0-4h5V8h-5v2zM9 8H4v2h5V8z" clip-rule="evenodd"></path></svg>
                            Excel
                        </button>
                    </div>
                </div>

                {{-- Step 4 (Admin) / Step 3 (Employee): Pratinjau Data --}}
                <div class="animate-fade-in">
                    <div class="flex items-center justify-between mb-3">
                        <label class="text-sm font-bold text-slate-700">
                            <span class="inline-flex items-center gap-2">
                                <span class="flex items-center justify-center w-6 h-6 rounded-full {{ $selectedDataType === 'pemeliharaan' ? 'bg-blue-100 text-blue-600' : 'bg-green-100 text-green-600' }} text-xs font-bold">
                                    {{ $isAdmin ? 4 : 3 }}
                                </span>
                                Pratinjau Data
                            </span>
                        </label>
                        @if(count($previewData) > 0)
                            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ count($previewData) }} baris</span>
                        @endif
                    </div>

                    {{-- CONTAINER UTAMA --}}
                    <div class="relative min-h-[250px] rounded-xl border border-slate-200 bg-white overflow-hidden">
                        
                        {{-- LOADING OVERLAY --}}
                        <div 
                            wire:loading.flex 
                            wire:target="selectedDataType, selectedMonth, selectedCategory" 
                            class="absolute inset-0 z-50 flex-col items-center justify-center bg-white/90 backdrop-blur-sm"
                        >
                            <div class="flex flex-col items-center p-4 bg-white rounded-2xl shadow-lg border border-slate-100">
                                <svg class="animate-spin h-10 w-10 text-blue-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm font-bold text-slate-700 animate-pulse">Sedang Memuat Data...</span>
                            </div>
                        </div>

                        {{-- KONTEN DATA --}}
                        <div class="w-full h-full">
                            @if (count($previewData) > 0)
                                <div class="overflow-x-auto max-h-[400px]">
                                    <table class="w-full text-xs text-left">
                                        <thead class="bg-slate-50 text-slate-600 uppercase tracking-wider sticky top-0 z-10 shadow-sm">
                                            <tr>
                                                @foreach (array_keys($previewData[0]) as $key)
                                                    <th class="px-4 py-3 font-bold whitespace-nowrap bg-slate-50 border-b border-slate-200">{{ str_replace('_', ' ', $key) }}</th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-slate-100">
                                            @foreach (array_slice($previewData, 0, 5) as $row)
                                                <tr class="hover:bg-blue-50/50 transition-colors">
                                                    @foreach ($row as $value)
                                                        <td class="px-4 py-3 text-slate-700 whitespace-nowrap">{{ $value }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @if (count($previewData) > 5)
                                    <div class="bg-slate-50 px-4 py-2 text-center border-t border-slate-200">
                                        <p class="text-xs text-slate-500 font-medium">Menampilkan 5 dari {{ count($previewData) }} data</p>
                                    </div>
                                @endif
                            @else
                                {{-- EMPTY STATE --}}
                                <div class="flex flex-col items-center justify-center h-[250px] w-full text-center p-6">
                                    <div class="p-4 bg-slate-50 rounded-full mb-3 shadow-sm">
                                        <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    </div>
                                    <p class="text-base font-medium text-slate-600">Tidak ada data ditampilkan</p>
                                    <p class="text-sm text-slate-400 mt-1 max-w-xs mx-auto">
                                        @if ($selectedDataType === 'pemeliharaan' && !$selectedMonth)
                                            Silakan pilih bulan di langkah {{ $isAdmin ? '2' : '1' }}
                                        @elseif ($selectedDataType === 'aset' && !$selectedCategory)
                                            Silakan pilih kategori di langkah 2
                                        @else
                                            Data tidak ditemukan untuk filter ini
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Download Button --}}
                <button
                    wire:click="export"
                    wire:loading.attr="disabled"
                    wire:target="export" 
                    @if (count($previewData) === 0) disabled @endif
                    class="w-full py-3.5 px-6 rounded-xl text-sm font-bold text-white transition-all flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed {{ count($previewData) > 0 ? 'bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 shadow-lg shadow-blue-500/30' : 'bg-slate-300' }}"
                >
                    <span wire:loading.remove wire:target="export">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    </span>
                    <span wire:loading wire:target="export">
                        <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <span wire:loading.remove wire:target="export">DOWNLOAD LAPORAN</span>
                    <span wire:loading wire:target="export">MEMPROSES...</span>
                </button>
            @endif

        </div>
    </div>
</div>