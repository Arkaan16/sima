<div class="container mx-auto px-4 py-2">
    <div class="max-w-7xl mx-auto">
        
        {{-- Header Section (Tidak Berubah) --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $asset->model->name ?? 'Detail Aset' }}</h1>
                
                <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                    {{-- 1. Asset Tag (ditebalkan sedikit biar jelas) --}}
                    <span class="font-mono font-medium text-gray-700">{{ $asset->asset_tag }}</span>

                    {{-- 2. Model Number (Muncul hanya jika ada datanya) --}}
                    @if(optional($asset->model)->model_number)
                        <span class="text-gray-300">|</span>
                        <span>Model: {{ $asset->model->model_number }}</span>
                    @endif
                </p>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                <a href="{{ route('admin.assets.index') }}" wire:navigate
                   class="inline-flex justify-center items-center px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Kembali
                </a>
                <a href="{{ route('admin.assets.edit', $asset->id) }}" wire:navigate
                class="inline-flex justify-center items-center px-4 py-2 bg-green-600 rounded-lg text-sm font-medium text-white hover:bg-green-700 transition w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit Aset
                </a>
            </div>
        </div>

        {{-- Image & QR Section (Tidak Berubah) --}}
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
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download Kode QR
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>

                            <div x-show="open" 
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="transform opacity-0 scale-95"
                                 x-transition:enter-end="transform opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="transform opacity-100 scale-100"
                                 x-transition:leave-end="transform opacity-0 scale-95"
                                 class="absolute bottom-full mb-2 w-full rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
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

        {{-- Detail Grid 1 (Informasi Dasar, Penugasan, Pembelian) - Tidak Berubah --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            {{-- Informasi Dasar --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 h-full">
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
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 h-full">
                <h2 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">Penugasan & Lokasi</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Lokasi</dt>
                        <dd class="text-sm text-right">
                            @if($asset->defaultLocation)
                                
                                @if($asset->defaultLocation->parent)
                                    {{-- KASUS 1: Lokasi Child (Punya Parent/Gedung) --}}
                                    <div class="flex flex-col items-end">
                                        {{-- Nama Gedung (Parent) --}}
                                        <span class="font-bold text-gray-900">
                                            {{ $asset->defaultLocation->parent->name }}
                                        </span>
                                        
                                        {{-- Nama Ruangan (Child) --}}
                                        <span class="text-xs text-gray-500 flex items-center gap-1">
                                            <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                            {{ $asset->defaultLocation->name }}
                                        </span>
                                    </div>

                                @else
                                    {{-- KASUS 2: Lokasi Parent (Langsung di Gedung/Lantai Utama) --}}
                                    <span class="font-medium text-gray-900">
                                        {{ $asset->defaultLocation->name }}
                                    </span>
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
                                {{-- Relasi ketemu --}}
                                {{ $asset->assignedTo->name }}
                            @elseif($asset->assigned_to_id)
                                {{-- ID ada tapi relasi tidak ketemu --}}
                                <span class="text-red-500 italic text-xs">
                                    Data Karyawan Terhapus (ID: {{ $asset->assigned_to_id }})
                                </span>
                            @else
                                {{-- Kosong --}}
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
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 h-full">
                <h2 class="text-base font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200">Pembelian & Garansi</h2>
                <dl class="space-y-3">
                    {{-- Pemasok --}}
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Pemasok</dt>
                        <dd class="text-sm text-gray-900">{{ $asset->supplier->name ?? '-' }}</dd>
                    </div>

                    {{-- Tanggal Beli --}}
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Tanggal Beli</dt>
                        <dd class="text-sm text-gray-900">{{ $asset->purchase_date ? $asset->purchase_date->format('d M Y') : '-' }}</dd>
                    </div>

                    {{-- Tanggal EOL (DITAMBAHKAN DISINI) --}}
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Tanggal Batas Layak Pakai</dt>
                        <dd class="text-sm text-gray-900">
                            {{-- Karena di model sudah di-cast 'date', kita bisa pakai format() --}}
                            {{ $asset->eol_date ? $asset->eol_date->format('d M Y') : '-' }}
                        </dd>
                    </div>

                    {{-- Harga --}}
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Harga</dt>
                        <dd class="text-sm font-semibold text-gray-900">
                            {{-- Ubah 0 menjadi 2 di sini --}}
                            {{ $asset->purchase_cost ? 'Rp ' . number_format($asset->purchase_cost, 2, ',', '.') : '-' }}
                        </dd>
                    </div>

                    {{-- Garansi --}}
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Garansi</dt>
                        <dd class="text-sm text-gray-900">
                            @if($asset->warranty_months && $asset->purchase_date)
                                @php
                                    // Hitung tanggal expired
                                    $exp = $asset->purchase_date->copy()->addMonths($asset->warranty_months);
                                @endphp

                                {{-- Tampilan: "12 Bulan" --}}
                                {{ $asset->warranty_months }} Bulan 
                                
                                {{-- Tampilan: "(Exp 17 Jun 2025)" --}}
                                {{-- Jika sudah lewat (isPast), warna merah. Jika belum, warna abu-abu biasa --}}
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
                        
                        {{-- 1. URL Website Resmi (Link Aktif - Icon Globe) --}}
                        @if($m->url)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                                <a href="{{ $m->url }}" target="_blank" class="text-blue-600 hover:underline truncate font-medium" title="Kunjungi Website Resmi">
                                    {{ parse_url($m->url, PHP_URL_HOST) ?? $m->url }}
                                </a>
                            </div>
                        @endif

                        {{-- 2. URL Halaman Support (Link Aktif - Icon Bantuan) --}}
                        {{-- Pastikan kolom 'support_url' ada di tabel manufacturers --}}
                        @if($m->support_url)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414"/>
                                </svg>
                                <a href="{{ $m->support_url }}" target="_blank" class="text-blue-600 hover:underline truncate font-medium" title="Kunjungi Halaman Support">
                                    Halaman Support
                                </a>
                            </div>
                        @endif

                        {{-- 3. Email Support (Teks Biasa - Tidak Bisa Diklik) --}}
                        @if($m->support_email)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-gray-700 truncate select-all">
                                    {{ $m->support_email }}
                                </span>
                            </div>
                        @endif

                        {{-- 4. Telp Support (Teks Biasa - Tidak Bisa Diklik) --}}
                        @if($m->support_phone)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span class="text-gray-700 select-all">
                                    {{ $m->support_phone }}
                                </span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-32 text-gray-400 text-sm italic bg-gray-50 rounded-lg">
                        <span>Data pabrikan tidak tersedia</span>
                    </div>
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
                        
                        {{-- 1. URL Pemasok (Tetap Link) --}}
                        @if($s->url)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                                <a href="{{ $s->url }}" target="_blank" class="text-blue-600 hover:underline truncate font-medium">
                                    {{ parse_url($s->url, PHP_URL_HOST) ?? $s->url }}
                                </a>
                            </div>
                        @endif

                        {{-- 2. Email Pemasok (TIDAK LINK) --}}
                        @if($s->email)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span class="text-gray-700 truncate select-all">
                                    {{ $s->email }}
                                </span>
                            </div>
                        @endif

                        {{-- 3. Telp Pemasok (TIDAK LINK) --}}
                        @if($s->phone)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span class="text-gray-700 select-all">
                                    {{ $s->phone }}
                                </span>
                            </div>
                        @endif
                        
                        @if($s->contact_name)
                            <div class="flex items-center pt-2 mt-2 border-t border-gray-100">
                                <svg class="w-4 h-4 mr-2 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                <span class="text-gray-900">{{ $s->contact_name }}</span>
                            </div>
                        @endif
                        
                        @if($s->address)
                            <div class="flex items-start">
                                <svg class="w-4 h-4 mr-2 text-gray-400 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span class="text-gray-500 text-xs">{{ $s->address }}</span>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-32 text-gray-400 text-sm italic bg-gray-50 rounded-lg">
                        <span>Data pemasok tidak tersedia</span>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>