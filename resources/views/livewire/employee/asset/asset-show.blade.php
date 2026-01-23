<div class="container mx-auto px-4 py-4 sm:py-6" x-data="{ activeTab: @entangle('activeTab') }">
    <div class="max-w-7xl mx-auto">
        
        {{-- BAGIAN 1: HEADER --}}
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4 mb-6">
            <div class="w-full lg:w-auto">
                <h1 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 leading-tight">
                    {{ $asset->model->name ?? 'Detail Aset' }}
                </h1>
                <p class="text-sm text-gray-500 mt-1 flex flex-wrap items-center gap-2">
                    <span class="font-mono font-medium text-gray-700 bg-gray-100 px-2 py-0.5 rounded">
                        {{ $asset->asset_tag }}
                    </span>
                    @if(optional($asset->model)->model_number)
                        <span class="hidden sm:inline text-gray-300">|</span>
                        <span class="text-xs sm:text-sm">Model: {{ $asset->model->model_number }}</span>
                    @endif
                </p>
            </div>
            
            {{-- Tombol Aksi --}}
            <div class="flex flex-col sm:flex-row gap-3 w-full lg:w-auto mt-2 lg:mt-0">
                {{-- Tombol Kembali --}}
                <a href="{{ route('employee.assets.index') }}" wire:navigate
                   class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition w-full sm:w-auto shadow-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>

                {{-- [DIHAPUS] Tombol Edit Aset --}}
            </div>
        </div>

        {{-- BAGIAN 2: NAVIGASI TAB --}}
        <div class="mb-6 overflow-x-auto overflow-y-hidden">
            <div class="border-b border-gray-200 min-w-full inline-block align-middle">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    
                    {{-- Tombol Tab Detail --}}
                    <button @click="activeTab = 'detail'"
                            :class="activeTab === 'detail' 
                                ? 'border-blue-500 text-blue-600' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors duration-200 group focus:outline-none">
                        <svg class="w-5 h-5 mr-2 group-hover:text-gray-600" :class="activeTab === 'detail' ? 'text-blue-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Detail Aset
                    </button>

                    {{-- Tombol Tab Riwayat --}}
                    <button @click="activeTab = 'history'"
                            :class="activeTab === 'history' 
                                ? 'border-blue-500 text-blue-600' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm flex items-center transition-colors duration-200 group focus:outline-none">
                        <svg class="w-5 h-5 mr-2 group-hover:text-gray-600" :class="activeTab === 'history' ? 'text-blue-500' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Riwayat Pemeliharaan
                        <span class="ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium" 
                              :class="activeTab === 'history' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600'">
                            {{ $asset->maintenances->count() }}
                        </span>
                    </button>
                    
                </nav>
            </div>
        </div>

        {{-- BAGIAN 3: KONTEN TAB --}}
        
        {{-- ================= TAB 1: DETAIL ================= --}}
        <div x-show="activeTab === 'detail'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0">
            
            {{-- Image & QR Section --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Foto Aset</h3>
                        @if($asset->image_url)
                            <div class="rounded-lg overflow-hidden bg-gray-50 border border-gray-200">
                                <img src="{{ $asset->image_url }}" alt="Foto Aset" class="w-full h-64 sm:h-80 object-cover">
                            </div>
                        @else
                            <div class="rounded-lg bg-gray-100 border-2 border-dashed border-gray-300 h-64 sm:h-80 flex items-center justify-center">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <p class="mt-2 text-sm text-gray-500">Tidak ada gambar</p>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="w-full flex flex-col">
                        <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">Kode QR</h3>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200 flex-grow flex flex-col justify-between items-center">
                            <div class="flex justify-center items-center flex-grow py-4">
                                @if($asset->qr_code_url)
                                    <img src="{{ $asset->qr_code_url }}" alt="QR Code" class="w-48 h-48 sm:w-56 sm:h-56 object-contain border-2 border-gray-300 rounded-lg bg-white p-2">
                                @else
                                    <div class="text-gray-400 text-sm italic">QR Code belum digenerate</div>
                                @endif
                            </div>
                            
                            <div class="relative w-full max-w-xs" x-data="{ open: false }">
                                <button @click="open = !open" @click.away="open = false" type="button" 
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition shadow-sm">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Download Kode QR
                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </button>

                                <div x-show="open" class="absolute bottom-full mb-2 w-full rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50" style="display: none;">
                                    <div class="py-1">
                                        <button wire:click="downloadQr('24')" @click="open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex justify-between items-center">
                                            <span>Besar (5cm)</span>
                                        </button>
                                        <button wire:click="downloadQr('18')" @click="open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 flex justify-between items-center">
                                            <span>Kecil (3.5cm)</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Grid 1 --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                {{-- Informasi Dasar --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h2 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">Informasi Dasar</h2>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Kode Aset</dt>
                            <dd class="text-sm font-semibold text-gray-900">{{ $asset->asset_tag }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Nomor Seri</dt>
                            <dd class="text-sm text-gray-900 font-mono">{{ $asset->serial ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Pabrikan</dt>
                            <dd class="text-sm text-gray-900">{{ $asset->model->manufacturer->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Kategori</dt>
                            <dd class="text-sm text-gray-900">{{ $asset->model->category->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between items-center">
                            <dt class="text-sm text-gray-500">Status</dt>
                            <dd>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    {{ $asset->status->name ?? '-' }}
                                </span>
                            </dd>
                        </div>
                    </dl>
                </div>

                {{-- Penugasan & Lokasi --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h2 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">Penugasan & Lokasi</h2>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Lokasi</dt>
                            <dd class="text-sm text-right">
                                @if($asset->defaultLocation)
                                    @if($asset->defaultLocation->parent)
                                        <div class="flex flex-col items-end">
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
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Ditugaskan Ke</dt>
                            <dd class="text-sm text-gray-900 font-medium text-right">
                                @if($asset->assignedTo)
                                    {{ $asset->assignedTo->name }}
                                @elseif($asset->assigned_to_id)
                                    <span class="text-red-500 italic text-xs">Data Karyawan Terhapus</span>
                                @else
                                    Milik Ruangan
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Terakhir Update</dt>
                            <dd class="text-sm text-gray-900">{{ $asset->updated_at->format('d M Y') }}</dd>
                        </div>
                    </dl>
                </div>

                {{-- Pembelian & Garansi --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <h2 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">Pembelian & Garansi</h2>
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Pemasok</dt>
                            <dd class="text-sm text-gray-900">{{ $asset->supplier->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Tanggal Beli</dt>
                            <dd class="text-sm text-gray-900">{{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Batas Layak Pakai</dt>
                            <dd class="text-sm text-gray-900">{{ $asset->eol_date ? $asset->eol_date->format('d M Y') : '-' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Harga</dt>
                            <dd class="text-sm font-semibold text-gray-900">
                                {{ $asset->purchase_cost ? 'Rp ' . number_format($asset->purchase_cost, 2, ',', '.') : '-' }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Garansi</dt>
                            <dd class="text-sm text-gray-900">
                                @if($asset->warranty_months && $asset->purchase_date)
                                    @php $exp = $asset->purchase_date->copy()->addMonths($asset->warranty_months); @endphp
                                    {{ $asset->warranty_months }} Bulan 
                                    <span class="{{ $exp->isPast() ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                        (Exp {{ $exp->format('d M Y') }})
                                    </span>
                                @else
                                    {{ $asset->warranty_months ? $asset->warranty_months . ' Bulan' : '-' }}
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            {{-- Detail Grid 2 (Pabrikan & Pemasok) --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- DETAIL PABRIKAN --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 h-full">
                    <div class="flex justify-between items-start mb-4 pb-3 border-b border-gray-200">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Detail Pabrikan</h2>
                            @if($asset->model && $asset->model->manufacturer)
                                <p class="text-sm text-gray-500">{{ $asset->model->manufacturer->name }}</p>
                            @endif
                        </div>
                        @if($asset->model && $asset->model->manufacturer && $asset->model->manufacturer->image)
                            <img src="{{ Storage::url($asset->model->manufacturer->image) }}" alt="Logo" class="h-10 w-auto object-contain">
                        @endif
                    </div>
                    
                    @if($asset->model && $asset->model->manufacturer)
                        @php $m = $asset->model->manufacturer; @endphp
                        <div class="space-y-3 text-sm">
                            @if($m->url)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                    <a href="{{ $m->url }}" target="_blank" class="text-blue-600 hover:underline truncate">{{ parse_url($m->url, PHP_URL_HOST) ?? $m->url }}</a>
                                </div>
                            @endif
                            @if($m->support_url)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                                    <span class="text-gray-500 mr-1">Support:</span>
                                    <a href="{{ $m->support_url }}" target="_blank" class="text-blue-600 hover:underline truncate">{{ parse_url($m->support_url, PHP_URL_HOST) ?? 'Link' }}</a>
                                </div>
                            @endif
                            @if($m->support_email)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span>{{ $m->support_email }}</span>
                                </div>
                            @endif
                            @if($m->support_phone)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span>{{ $m->support_phone }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-32 text-gray-400 text-sm italic bg-gray-50 rounded-lg">Data pabrikan tidak tersedia</div>
                    @endif
                </div>

                {{-- DETAIL PEMASOK --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 h-full">
                    <div class="flex justify-between items-start mb-4 pb-3 border-b border-gray-200">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900">Detail Pemasok</h2>
                            @if($asset->supplier)
                                <p class="text-sm text-gray-500">{{ $asset->supplier->name }}</p>
                            @endif
                        </div>
                        @if($asset->supplier && $asset->supplier->image)
                            <img src="{{ Storage::url($asset->supplier->image) }}" alt="Logo" class="h-10 w-auto object-contain">
                        @endif
                    </div>

                    @if($asset->supplier)
                        @php $s = $asset->supplier; @endphp
                        <div class="space-y-3 text-sm">
                            @if($s->address)
                                <div class="flex items-start">
                                    <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span class="text-gray-700 leading-tight">{{ $s->address }}</span>
                                </div>
                            @endif
                            @if($s->url)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                                    <a href="{{ $s->url }}" target="_blank" class="text-blue-600 hover:underline truncate">{{ parse_url($s->url, PHP_URL_HOST) ?? $s->url }}</a>
                                </div>
                            @endif
                            @if($s->email)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span>{{ $s->email }}</span>
                                </div>
                            @endif
                            @if($s->contact_name)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span class="text-gray-900">{{ $s->contact_name }}</span>
                                </div>
                            @endif
                            @if($s->phone)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span>{{ $s->phone }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center h-32 text-gray-400 text-sm italic bg-gray-50 rounded-lg">Data pemasok tidak tersedia</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ================= TAB 2: RIWAYAT PEMELIHARAAN ================= --}}
        <div x-show="activeTab === 'history'" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             style="display: none;">
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                
                {{-- HEADER SECTION: Judul & Tombol Tambah --}}
                <div class="p-5 border-b border-gray-200 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
                    {{-- Judul & Counter --}}
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Daftar Aktivitas Pemeliharaan</h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Total: <span class="font-medium text-gray-900">{{ $asset->maintenances->count() }}</span> catatan.
                        </p>
                    </div>

                    {{-- [DIKEMBALIKAN] Tombol Tambah Data Baru --}}
                    {{-- Pastikan route 'employee.maintenances.create' sudah dibuat di web.php {{ route('employee.maintenances.create', ['asset_tag' => $asset->asset_tag]) }} --}}
                    <a href="#" 
                       wire:navigate
                       class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 border border-transparent rounded-lg text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition shadow-sm w-full sm:w-auto">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Data Baru
                    </a>
                </div>

                @if($asset->maintenances->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Detail Aktivitas</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Jenis</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Teknisi</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($asset->maintenances as $maintenance)
                                    <tr class="hover:bg-gray-50 transition duration-150 ease-in-out">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex items-center">
                                                <svg class="w-4 h-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ $maintenance->execution_date->format('d M Y') }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-semibold text-gray-900 mb-0.5">{{ $maintenance->title }}</div>
                                            <div class="text-xs text-gray-500 line-clamp-2 max-w-xs" title="{{ $maintenance->description }}">{{ Str::limit($maintenance->description, 60) }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $maintenance->badge_class }}">
                                                {{ $maintenance->type_label }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($maintenance->technicians->count() > 0)
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-6 w-6 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-600 border border-blue-200">
                                                        {{ substr($maintenance->technicians->first()->name, 0, 1) }}
                                                    </div>
                                                    <span class="ml-2 font-medium text-gray-700">{{ $maintenance->technicians->first()->name }}</span>
                                                    @if($maintenance->technicians->count() > 1)
                                                        <span class="ml-1 text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-full">+{{ $maintenance->technicians->count() - 1 }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400 italic text-xs">- Tidak ada teknisi -</span>
                                            @endif
                                        </td>
                                        {{-- Kolom Aksi (Hanya Lihat Detail) --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            {{-- Link ke Detail Maintenance Employee {{ route('employee.maintenances.show', ['maintenance' => $maintenance->id]) }}--}}
                                            <a href="#" 
                                               wire:navigate 
                                               class="inline-flex items-center text-blue-600 hover:text-blue-900 transition-colors group">
                                                <span class="group-hover:underline">Lihat Detail</span>
                                                <svg class="w-4 h-4 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $maintenances->links() }}
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-16 text-center px-4">
                        <div class="bg-gray-50 rounded-full p-6 mb-4 ring-1 ring-gray-100">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900">Belum ada riwayat pemeliharaan</h3>
                        <p class="text-gray-500 text-sm mt-1 max-w-sm mx-auto">Aset ini belum memiliki catatan perawatan. Klik tombol "Tambah Data Baru" di atas untuk memulai pencatatan.</p>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>