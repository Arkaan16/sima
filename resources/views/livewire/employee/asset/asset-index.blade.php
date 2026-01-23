<div class="container mx-auto px-4 max-w-7xl">
    
    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        {{-- JUDUL HALAMAN --}}
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Daftar Aset</h1>
            <p class="text-sm text-gray-500 mt-1">Lihat dan cari data aset perusahaan.</p>
        </div>
        
        {{-- WRAPPER TOMBOL AKSI --}}
        {{-- 1. w-full sm:w-auto: Agar wrapper ini mengambil lebar penuh di mobile --}}
        <div class="flex flex-wrap gap-2 w-full sm:w-auto"> 
            
            {{-- DROPDOWN DOWNLOAD --}}
            {{-- 2. relative w-full sm:w-auto: Agar dropdown parent menyesuaikan lebar --}}
            <div x-data="{ open: false }" class="relative w-full sm:w-auto">
                
                {{-- TOMBOL UTAMA --}}
                {{-- 3. w-full sm:w-auto & justify-center: Agar tombol full & teks di tengah saat mobile --}}
                <button @click="open = !open" type="button" 
                    class="bg-purple-100 hover:bg-purple-200 text-purple-700 text-sm font-medium py-2.5 px-4 rounded-xl transition flex items-center justify-center border border-purple-200 w-full sm:w-auto">
                    
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    Unduh Semua QR
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>

                {{-- MENU DROPDOWN --}}
                {{-- 4. w-full sm:w-48: Agar isi dropdown juga full width di mobile --}}
                <div x-show="open" @click.away="open = false" style="display: none;" 
                    class="absolute right-0 mt-2 w-full sm:w-48 bg-white rounded-xl shadow-lg border border-gray-100 z-50 overflow-hidden">
                    
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-100">
                        <p class="text-xs font-semibold text-gray-500 uppercase">Pilih Ukuran</p>
                    </div>
                    
                    <button wire:click="downloadBulkQr('18')" @click="open = false" class="w-full text-left px-4 py-3 hover:bg-purple-50 text-sm text-gray-700 hover:text-purple-700 transition flex justify-between items-center">
                        <span>Ukuran Kecil (18)</span>
                        <span class="text-xs text-gray-400">~35mm</span>
                    </button>

                    <button wire:click="downloadBulkQr('24')" @click="open = false" class="w-full text-left px-4 py-3 hover:bg-purple-50 text-sm text-gray-700 hover:text-purple-700 transition flex justify-between items-center">
                        <span>Ukuran Sedang (24)</span>
                        <span class="text-xs text-gray-400">~50mm</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ALERT MESSAGE --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
             class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex items-center">
             <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
             {{ session('message') }}
        </div>
    @endif

    {{-- FILTERS --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        
        {{-- Search --}}
        <div class="relative col-span-1 md:col-span-2">
            <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" 
                placeholder="Cari tag, nama model, lokasi atau ruangan, nomor serial..." 
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm shadow-sm transition bg-white">
        </div>

        {{-- Filter Kategori --}}
        <select wire:model.live="category_id" class="px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm shadow-sm transition bg-white">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>

        {{-- Filter Status --}}
        <select wire:model.live="status_id" class="px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm shadow-sm transition bg-white">
            <option value="">Semua Status</option>
            @foreach($statuses as $st)
                <option value="{{ $st->id }}">{{ $st->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- TABLE CARD --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aset</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Lokasi</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($assets as $asset)
                    <tr class="hover:bg-gray-50 transition">
                        
                        {{-- Kolom Aset --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    <img class="h-10 w-10 rounded-lg object-cover border border-gray-200" src="{{ $asset->image_url }}" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">
                                        {{ $asset->name ?? $asset->model->name }}
                                        
                                        @if(optional($asset->model)->model_number)
                                            <span class="ml-1 font-normal text-gray-500 text-xs">
                                                ({{ $asset->model->model_number }})
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <div class="text-xs text-gray-500 font-mono">
                                        {{ $asset->asset_tag }} 
                                        @if($asset->serial) <span class="text-gray-300 mx-1">|</span> SN: {{ $asset->serial }} @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Kolom Kategori --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                {{ $asset->model->category->name ?? '-' }}
                            </span>
                        </td>

                        {{-- Kolom Lokasi --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($asset->defaultLocation)
                                @if($asset->defaultLocation->parent)
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900">
                                            {{ $asset->defaultLocation->parent->name }}
                                        </span>
                                        <span class="text-xs text-gray-500 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            {{ $asset->defaultLocation->name }}
                                        </span>
                                    </div>
                                @else
                                    <span class="font-medium text-gray-900">
                                        {{ $asset->defaultLocation->name }}
                                    </span>
                                @endif
                            @else
                                <span class="text-gray-400 italic">-</span>
                            @endif
                        </td>

                        {{-- Kolom Status --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColor = match($asset->status->name ?? '') {
                                    'Ready', 'Aktif', 'Tersedia','Siap Di Deploy' => 'bg-green-100 text-green-800',
                                    'Rusak', 'Broken' => 'bg-red-100 text-red-800',
                                    'Maintenance', 'Perbaikan' => 'bg-yellow-100 text-yellow-800',
                                    'Dipinjam', 'Deployed' => 'bg-blue-100 text-blue-800',
                                    default => 'bg-gray-100 text-gray-800'
                                };
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColor }}">
                                {{ $asset->status->name ?? '-' }}
                            </span>
                        </td>

                        {{-- Kolom Aksi (Hanya Detail) --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                            <div class="flex justify-center space-x-3">
                                
                                {{-- Tombol Detail (Satu-satunya tombol aksi)  --}}
                                {{-- Pastikan Anda sudah membuat route 'employee.assets.show' atau sesuaikan routenya --}}
                                <a href="{{ route('employee.assets.show', $asset->asset_tag) }}" wire:navigate 
                                class="text-blue-600 hover:text-blue-900 transition" 
                                title="Lihat Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 italic bg-gray-50">
                            Belum ada data aset yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $assets->links() }}
        </div>
    </div> 
</div>
