<div class="container mx-auto px-4 max-w-7xl">
    
    {{-- HEADER --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Kelola Aset</h1>
        </div>
        
        
        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
            
            {{-- 1. TOMBOL TAMBAH (HANYA ADMIN) --}}
            @can('create', App\Models\Asset::class)
            
            <a href="{{ route('assets.create') }}" wire:navigate 
               class="w-full sm:w-auto justify-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-2.5 px-4 rounded-xl transition flex items-center shadow-lg shadow-blue-500/30">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                Tambah Aset
            </a>
            @endcan
            
            {{-- 2. TOMBOL UNDUH QR --}}
            @can('downloadQr', App\Models\Asset::class)
            
            <div x-data="{ open: false }" class="relative w-full sm:w-auto">          
                <button @click="open = !open" type="button" 
                        class="w-full justify-center bg-purple-100 hover:bg-purple-200 text-purple-700 text-sm font-medium py-2.5 px-4 rounded-xl transition flex items-center border border-purple-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                    </svg>
                    Unduh Semua QR
                    <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>

                {{-- Dropdown Content --}}
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
            @endcan
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

    {{-- FILTERS (SEMUA BISA LIHAT) --}}
    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="relative col-span-1 md:col-span-2">
            <svg class="absolute left-3 top-3 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            <input wire:model.live.debounce.300ms="search" type="text" 
                placeholder="Cari tag, nama model, lokasi atau ruangan, nomor serial..." 
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm shadow-sm transition bg-white">
        </div>
        <select wire:model.live="category_id" class="px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm shadow-sm transition bg-white">
            <option value="">Semua Kategori</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
            @endforeach
        </select>
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
                                    @php
                                        $finalImageUrl = null;

                                        // 1. Cek Gambar Spesifik Aset (Prioritas Utama)
                                        if (!empty($asset->image) && \Illuminate\Support\Facades\Storage::disk('public')->exists($asset->image)) {
                                            $finalImageUrl = asset('storage/' . $asset->image);
                                        } 
                                        // 2. Jika Gagal, Cek Gambar Master Model (Fallback)
                                        elseif (!empty($asset->model->image) && \Illuminate\Support\Facades\Storage::disk('public')->exists($asset->model->image)) {
                                            $finalImageUrl = asset('storage/' . $asset->model->image);
                                        }
                                    @endphp

                                    @if($finalImageUrl)
                                        {{-- Tampilkan Gambar Valid --}}
                                        <img class="h-10 w-10 rounded-lg object-cover border border-gray-200" 
                                            src="{{ $finalImageUrl }}" 
                                            alt="Foto Aset">
                                    @else
                                        {{-- Tampilkan Placeholder (Jika Aset & Model tidak punya gambar atau file hilang) --}}
                                        <div class="h-10 w-10 rounded-lg border border-gray-200 bg-gray-50 flex items-center justify-center text-gray-400">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-bold text-gray-900">
                                        {{ $asset->name ?? $asset->model->name }}
                                        @if(optional($asset->model)->model_number)
                                            <span class="ml-1 font-normal text-gray-500 text-xs">({{ $asset->model->model_number }})</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 font-mono">
                                        {{ $asset->asset_tag }} 
                                        @if($asset->serial) <span class="text-gray-300 mx-1">|</span> SN: {{ $asset->serial }} @endif
                                    </div>
                                </div>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700">
                                {{ $asset->model->category->name ?? '-' }}
                            </span>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                            @if($asset->defaultLocation)
                                @if($asset->defaultLocation->parent)
                                    <div class="flex flex-col">
                                        <span class="font-bold text-gray-900">{{ $asset->defaultLocation->parent->name }}</span>
                                        <span class="text-xs text-gray-500 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                            {{ $asset->defaultLocation->name }}
                                        </span>
                                    </div>
                                @else
                                    <span class="font-medium text-gray-900">{{ $asset->defaultLocation->name }}</span>
                                @endif
                            @else
                                <span class="text-gray-400 italic">-</span>
                            @endif
                        </td>

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

                        {{-- KOLOM AKSI: TAMPIL UNTUK SEMUA (Tombol di dalam difilter oleh @can) --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                            <div class="flex justify-center space-x-3">
                                
                                {{-- 1. TOMBOL LIHAT (Muncul untuk Admin & Employee) --}}
                                @can('view', $asset)
                                <a href="{{ route('assets.show', $asset->asset_tag) }}" wire:navigate 
                                class="text-blue-600 hover:text-blue-900 transition" 
                                title="Lihat Detail">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                                @endcan

                                {{-- 2. TOMBOL EDIT (Hanya muncul jika Policy 'update' true / Admin) --}}
                                @can('update', $asset)
                                <a href="{{ route('assets.edit', $asset->id) }}" wire:navigate
                                class="text-green-600 hover:text-green-900 transition" 
                                title="Edit Aset">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                </a>
                                @endcan

                                {{-- 3. TOMBOL HAPUS (Hanya muncul jika Policy 'delete' true / Admin) --}}
                                @can('delete', $asset)
                                <button wire:click="confirmDelete({{ $asset->id }})" 
                                        class="text-red-600 hover:text-red-900 transition"
                                        title="Hapus Aset">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 011 1v6a1 1 0 11-2 0V8a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                @endcan

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        {{-- Colspan 5 karena kolom Aksi sekarang muncul untuk semua role --}}
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic bg-gray-50">
                            <div class="flex flex-col items-center justify-center">
                                {{-- Icon Folder Kosong (Optional, biar lebih bagus) --}}
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p>Belum ada data aset.</p>
                            </div>
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

    {{-- MODAL DELETE --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div wire:click="closeModal" class="fixed inset-0 bg-gray-900/60 transition-opacity" aria-hidden="true"></div>
            <div class="bg-white rounded-2xl p-6 shadow-2xl transform transition-all sm:max-w-sm w-full z-10 relative border border-gray-100">
                <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="text-center">
                    <h3 class="text-xl font-bold text-gray-900" id="modal-title">Hapus Aset?</h3>
                    <div class="mt-2">
                        <p class="text-gray-500 text-sm leading-relaxed">
                            Apakah Anda yakin ingin menghapus aset <br>
                            <span class="font-bold text-gray-800">"{{ $deleteName }}"</span>? <br>
                            <span class="text-red-500 font-medium text-xs">Data yang dihapus tidak dapat dikembalikan.</span>
                        </p>
                    </div>
                </div>
                <div class="mt-8 flex gap-3">
                    <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold transition hover:bg-gray-200 focus:outline-none">Batal</button>
                    <button type="button" wire:click="delete" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl font-bold flex items-center justify-center transition hover:bg-red-700 focus:outline-none disabled:opacity-50 shadow-lg shadow-red-500/30">
                        <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                        <svg wire:loading wire:target="delete" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

</div>