<div class="container mx-auto px-4 max-w-5xl">
    <div class="text-center mb-8">
        <h1 class="text-2xl font-bold text-slate-800">Ekspor Laporan Saya</h1>
        <p class="text-slate-500 text-sm mt-1">Unduh riwayat pekerjaan pemeliharaan Anda</p>
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

            {{-- Step 1: Filter Bulan --}}
            <div>
                <label class="block text-sm font-bold text-slate-700 mb-3">
                    <span class="inline-flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 text-xs font-bold">1</span>
                        Pilih Periode Bulan <span class="text-red-500">*</span>
                    </span>
                </label>
                
                <div wire:loading.class="opacity-50 pointer-events-none" wire:target="selectedMonth">
                    <select
                        wire:model.live="selectedMonth"
                        class="block w-full px-4 py-3 text-sm border-slate-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 rounded-xl bg-white shadow-sm"
                    >
                        <option value="">-- Pilih Bulan --</option>
                        @foreach ($months as $month)
                            <option value="{{ $month['value'] }}">{{ $month['label'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Step 2: Format Ekspor --}}
            <div class="animate-fade-in">
                <label class="block text-sm font-bold text-slate-700 mb-3">
                    <span class="inline-flex items-center gap-2">
                        <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 text-xs font-bold">2</span>
                        Format Ekspor
                    </span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        wire:click="$set('selectedFormat', 'pdf')"
                        class="flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-semibold border-2 transition-all {{ $selectedFormat === 'pdf' ? 'bg-red-600 border-red-600 text-white shadow-md' : 'bg-white border-slate-200 text-slate-600 hover:border-red-300' }}"
                    >
                        PDF
                    </button>

                    <button
                        wire:click="$set('selectedFormat', 'excel')"
                        class="flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-semibold border-2 transition-all {{ $selectedFormat === 'excel' ? 'bg-green-600 border-green-600 text-white shadow-md' : 'bg-white border-slate-200 text-slate-600 hover:border-green-300' }}"
                    >
                        Excel
                    </button>
                </div>
            </div>

            {{-- Step 3: Pratinjau Data --}}
            <div class="animate-fade-in">
                <div class="flex items-center justify-between mb-3">
                    <label class="text-sm font-bold text-slate-700">
                        <span class="inline-flex items-center gap-2">
                            <span class="flex items-center justify-center w-6 h-6 rounded-full bg-blue-100 text-blue-600 text-xs font-bold">3</span>
                            Pratinjau Data
                        </span>
                    </label>
                    @if(count($previewData) > 0)
                        <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ count($previewData) }} baris</span>
                    @endif
                </div>

                <div class="relative min-h-[250px] rounded-xl border border-slate-200 bg-white overflow-hidden">
                    
                    {{-- Loading Overlay --}}
                    <div 
                        wire:loading.flex 
                        wire:target="selectedMonth" 
                        class="absolute inset-0 z-50 flex-col items-center justify-center bg-white/90 backdrop-blur-sm"
                    >
                        <div class="flex flex-col items-center p-4 bg-white rounded-2xl shadow-lg border border-slate-100">
                            <svg class="animate-spin h-10 w-10 text-blue-600 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-bold text-slate-700 animate-pulse">Memuat Data Saya...</span>
                        </div>
                    </div>

                    {{-- Data Table --}}
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
                            <div class="flex flex-col items-center justify-center h-[250px] w-full text-center p-6">
                                <div class="p-4 bg-slate-50 rounded-full mb-3 shadow-sm">
                                    <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </div>
                                <p class="text-base font-medium text-slate-600">Tidak ada data pekerjaan Anda.</p>
                                <p class="text-sm text-slate-400 mt-1">
                                    @if (!$selectedMonth)
                                        Silakan pilih bulan di langkah 1
                                    @else
                                        Anda belum memiliki riwayat pemeliharaan di bulan ini.
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
                    <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </span>
                <span wire:loading.remove wire:target="export">DOWNLOAD LAPORAN</span>
                <span wire:loading wire:target="export">MEMPROSES...</span>
            </button>

        </div>
    </div>
</div>