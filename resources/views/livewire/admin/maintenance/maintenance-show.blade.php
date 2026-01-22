{{-- 
    WRAPPER UTAMA: 
    - Added 'overflow-x-hidden': Mencegah scroll horizontal jika ada elemen bandel.
--}}
<div class="min-h-screen w-full font-sans text-gray-900 pb-20 overflow-x-hidden bg-gray-50">
    <div class="container mx-auto px-4 max-w-6xl">
        {{-- HEADER --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div class="w-full md:w-auto">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight leading-tight break-words">
                    {{ $maintenance->title }}
                </h1>
            </div>
            
            <a href="{{ $backUrl }}"
                wire:navigate class="group flex items-center justify-center gap-2 w-full md:w-auto bg-white text-gray-700 py-2.5 px-6 rounded-xl border border-gray-200 hover:border-blue-400 hover:text-blue-600 transition-all shadow-sm font-medium touch-manipulation">
                <svg class="w-4 h-4 text-gray-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali
            </a>
        </div>

        {{-- 
            GRID SYSTEM:
            - Tetap sama logikanya, tapi card di dalamnya diperkuat.
        --}}
        <div class="flex flex-col lg:grid lg:grid-cols-3 gap-6 items-start">

            {{-- 
                1. CARD ASET 
            --}}
            <div class="order-1 lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden w-full">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                    <div class="p-1.5 bg-blue-100 rounded-lg text-blue-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    </div>
                    <h2 class="font-bold text-gray-800 truncate">Objek Aset</h2>
                </div>
                
                <div class="p-5 flex flex-col sm:flex-row gap-6">
                    {{-- Foto Aset --}}
                    <div class="shrink-0 w-full sm:w-40">
                        @if($maintenance->asset->image || $maintenance->asset->model->image)
                            <img src="{{ asset('storage/' . ($maintenance->asset->image ?? $maintenance->asset->model->image)) }}" 
                                 class="w-full h-48 sm:h-32 object-cover rounded-xl border border-gray-100 shadow-sm block">
                        @else
                            <div class="w-full h-32 bg-gray-100 rounded-xl flex items-center justify-center text-gray-400 border border-gray-200 border-dashed">
                                <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        @endif
                    </div>

                    {{-- Info Aset --}}
                    {{-- KEY FIX: 'min-w-0' di sini mencegah text panjang mendorong layout mobile keluar --}}
                    <div class="flex-1 space-y-4 min-w-0">
                        <div class="w-full">
                            <label class="text-xs text-gray-500 uppercase tracking-wider font-bold">Nama Model / Aset</label>
                            {{-- Added 'break-words': Nama aset panjang akan turun baris --}}
                            <p class="text-lg font-bold text-gray-900 leading-tight mt-1 break-words">
                                {{ $maintenance->asset->model->name ?? '-' }}
                            </p>
                        </div>
                        
                        <div class="flex flex-wrap gap-4">
                            <div class="min-w-0 max-w-full">
                                <label class="text-xs text-gray-500 uppercase tracking-wider font-bold">Asset Tag</label>
                                <div class="mt-1">
                                    {{-- Added 'break-all': Tag panjang (hash) akan dipotong paksa jika perlu --}}
                                    <span class="inline-block max-w-full px-2.5 py-1 rounded-md text-sm font-medium bg-gray-100 text-gray-700 border border-gray-200 font-mono break-all">
                                        #{{ $maintenance->asset->asset_tag }}
                                    </span>
                                </div>
                            </div>
                            @if($maintenance->asset->serial)
                            <div class="min-w-0 max-w-full">
                                <label class="text-xs text-gray-500 uppercase tracking-wider font-bold">Serial Number</label>
                                <div class="mt-1 text-sm text-gray-700 font-medium font-mono break-all">
                                    {{ $maintenance->asset->serial }}
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- 
                2. CARD DESKRIPSI 
            --}}
            <div class="order-2 lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden w-full">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center gap-3">
                    <div class="p-1.5 bg-orange-100 rounded-lg text-orange-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h2 class="font-bold text-gray-800">Detail Pekerjaan</h2>
                </div>
                <div class="p-5">
                    {{-- KEY FIX: 'break-words' and 'break-all' agar link panjang tidak overflow --}}
                    <div class="prose prose-sm md:prose-base max-w-none text-gray-700 leading-relaxed break-words overflow-hidden">
                        {!! nl2br(e($maintenance->description)) !!}
                    </div>
                </div>
            </div>

            {{-- 
                3. SIDEBAR (STATUS & TEKNISI)
            --}}
            <div class="order-3 lg:col-start-3 lg:row-start-1 lg:row-span-3 space-y-6 w-full">
                
                {{-- Card Status --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 w-full">
                    <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">Jenis & Waktu</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <span class="block text-xs text-gray-500 mb-1 font-semibold">Jenis Pemeliharaan</span>
                            <span class="inline-flex items-center justify-center w-full px-3 py-2 rounded-lg text-sm font-bold border text-center break-words {{ $maintenance->badge_class }}">
                                {{ $maintenance->type_label }}
                            </span>
                        </div>

                        <div>
                            <span class="block text-xs text-gray-500 mb-1 font-semibold">Tanggal Pelaksanaan</span>
                            <div class="flex items-center text-gray-900 font-medium bg-gray-50 px-3 py-2 rounded-lg border border-gray-100">
                                <svg class="w-4 h-4 mr-2 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                <span class="truncate">
                                    {{ \Carbon\Carbon::parse($maintenance->execution_date)->translatedFormat('d F Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card Teknisi --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-5 w-full">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100">
                        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Teknisi Bertugas</h3>
                        <span class="bg-gray-100 text-gray-600 text-[10px] px-2 py-0.5 rounded-full font-bold">{{ $maintenance->technicians->count() }}</span>
                    </div>
                    
                    @if($maintenance->technicians->count() > 0)
                        <div class="space-y-3">
                            @foreach($maintenance->technicians as $tech)
                                <div class="flex items-center gap-3">
                                    <div class="h-8 w-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold ring-2 ring-white shadow-sm shrink-0">
                                        {{ substr($tech->name, 0, 1) }}
                                    </div>
                                    {{-- KEY FIX: min-w-0 agar truncate berfungsi --}}
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-gray-800 truncate" title="{{ $tech->name }}">
                                            {{ $tech->name }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                            <p class="text-sm text-gray-400 italic">Belum ada teknisi.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 
                4. CARD DOKUMENTASI 
            --}}
            <div class="order-4 lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 w-full" x-data="{ imgModal: null }">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-1.5 bg-purple-100 rounded-lg text-purple-600 shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <h2 class="font-bold text-gray-800 truncate">Dokumentasi</h2>
                    </div>
                    <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full border border-gray-200 font-medium shrink-0">
                        {{ count($maintenance->images) }} Foto
                    </span>
                </div>
                
                <div class="p-5">
                    @if($maintenance->images && count($maintenance->images) > 0)
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 md:gap-4">
                            @foreach($maintenance->images as $photo)
                                <div class="relative group cursor-pointer overflow-hidden rounded-xl bg-gray-50 border border-gray-200 aspect-[4/3] w-full"
                                     @click="imgModal = '{{ asset('storage/' . $photo->photo_path) }}'">
                                    
                                    <img src="{{ asset('storage/' . $photo->photo_path) }}" 
                                             class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                                             loading="lazy">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition-colors"></div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-10 bg-gray-50 border-2 border-dashed border-gray-200 rounded-xl">
                            <svg class="w-10 h-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span class="text-sm text-gray-500">Tidak ada foto.</span>
                        </div>
                    @endif
                </div>

                {{-- Lightbox Modal --}}
                <div x-show="imgModal" style="display: none;" 
                     class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/95 backdrop-blur-sm p-4"
                     x-transition.opacity>
                    
                    <button @click="imgModal = null" class="absolute top-4 right-4 text-white/80 hover:text-white p-2 bg-white/10 rounded-full z-50">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>

                    <div @click.away="imgModal = null" class="relative w-full max-w-5xl flex items-center justify-center">
                        <img :src="imgModal" class="max-w-full max-h-[85vh] rounded-lg shadow-2xl object-contain">
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>