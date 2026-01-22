<div class="container mx-auto px-4">
    <div class="max-w-4xl mx-auto">
        
        {{-- HEADER (Optimized: Stack di Mobile, Row di Desktop) --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Tambah Data Pemeliharaan</h1>
            
            <a href="{{ route('admin.maintenances.index') }}" wire:navigate class="w-full md:w-auto justify-center bg-gray-100 hover:bg-gray-200 text-gray-700 py-2 px-4 rounded-lg transition duration-150 ease-in-out shadow-sm flex items-center border border-gray-300">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>
        </div>

        {{-- FLASH MESSAGES --}}
        @if (session()->has('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg shadow-sm flex items-center">
                <svg class="w-6 h-6 text-green-500 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <p class="text-green-700 font-medium text-sm md:text-base">{{ session('success') }}</p>
            </div>
        @endif

        {{-- FORM CARD --}}
        <div class="bg-white rounded-xl shadow-lg p-6 md:p-8">
            <form wire:submit.prevent="save">
                <div class="space-y-6">

                    {{-- BAGIAN 1: Aset & Judul --}}
                    <div class="grid grid-cols-1 gap-6">
                        <div class="md:col-span-2" x-data="{ openAsset: false }">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Kode Aset <span class="text-red-500">*</span>
                            </label>
                            
                            <div class="relative">
                                
                                {{-- BUTTON TRIGGER --}}
                                <button type="button" @click="openAsset = !openAsset" 
                                    class="w-full bg-white border rounded-md px-3 py-2 flex items-center justify-between shadow-sm outline-none transition text-left sm:text-sm
                                    @error('form.asset_id') border-red-500 ring-1 ring-red-500 @else border-gray-300 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @enderror">
                                    
                                    <span class="flex items-center gap-3 truncate">
                                        @if($selectedAssetDisplay)
                                            {{-- Tampilkan Gambar (Prioritas: Gambar Aset -> Gambar Model) --}}
                                            @if($selectedAssetImage)
                                                <img src="{{ asset('storage/'.$selectedAssetImage) }}" class="h-6 w-6 rounded-md object-cover bg-gray-50 border border-gray-200 shrink-0">
                                            @endif
                                            
                                            {{-- Teks Pilihan --}}
                                            <span class="block truncate text-gray-900 font-medium">{{ $selectedAssetDisplay }}</span>
                                        @else
                                            <span class="block truncate text-gray-400">-- Cari Aset berdasarkan Tag/Model --</span>
                                        @endif
                                    </span>

                                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </span>
                                </button>
                                
                                {{-- LIST DROPDOWN --}}
                                <div x-show="openAsset" @click.away="openAsset = false" style="display: none;" 
                                    class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                                    
                                    {{-- Input Pencarian --}}
                                    <div class="px-2 py-2 border-b border-gray-100 bg-gray-50">
                                        <input type="text" wire:model.live.debounce.300ms="searchAsset" 
                                            class="block w-full border border-gray-300 rounded-md px-3 py-1.5 leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" 
                                            placeholder="Cari Asset Tag, Model, atau SN..." autofocus>
                                    </div>

                                    <ul class="max-h-48 overflow-y-auto">
                                        @forelse($assets as $asset)
                                            @php
                                                // Logika Penamaan & Gambar
                                                $assetTag = $asset->asset_tag;
                                                $modelName = $asset->model->name ?? 'Unknown Model';
                                                $sn = $asset->serial_number ?? '-';
                                                
                                                // Format Tampilan: Asset Tag - Model Name - (SN)
                                                $displayString = "{$assetTag} - {$modelName} - ({$sn})";
                                                
                                                // Ambil gambar (Aset punya gambar? kalau tidak ambil dari Model)
                                                // Asumsi kolom gambar di tabel assets adalah 'image', dan di models adalah 'image'
                                                $imagePath = $asset->image ?? $asset->model->image ?? null; 
                                            @endphp

                                            <li wire:click="selectAsset({{ $asset->id }}, '{{ $displayString }}', '{{ $imagePath }}'); openAsset = false" 
                                                class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 group transition-colors">
                                                
                                                <div class="flex items-center">
                                                    {{-- Gambar di List --}}
                                                    @if($imagePath)
                                                        <img src="{{ asset('storage/'.$imagePath) }}" class="h-9 w-9 rounded-md object-cover bg-white border mr-3 shrink-0">
                                                    @else
                                                        {{-- Placeholder jika tidak ada gambar --}}
                                                        <div class="h-9 w-9 rounded-md bg-gray-100 border mr-3 shrink-0 flex items-center justify-center text-xs text-gray-400 font-bold">
                                                            N/A
                                                        </div>
                                                    @endif
                                                    
                                                    <div class="flex flex-col overflow-hidden">
                                                        {{-- Baris 1: Asset Tag - Model --}}
                                                        <span class="font-medium block truncate text-sm">
                                                            <span class="font-bold text-blue-600 group-hover:text-blue-200">{{ $assetTag }}</span> 
                                                            - {{ $modelName }}
                                                        </span>                                                      
                                                    </div>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="text-gray-500 select-none relative py-2 pl-3 pr-9 italic text-center text-xs">
                                                Aset "{{ $searchAsset }}" tidak ditemukan.
                                            </li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                            
                            @error('form.asset_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700 mb-1">
                                Judul Aktivitas <span class="text-red-500">*</span>
                            </label>
                            
                            {{-- 2. Ubah class input agar visualnya (tinggi & sudut) sama dengan tombol aset --}}
                            {{--    Ganti 'rounded-lg' menjadi 'rounded-md' --}}
                            {{--    Ganti 'p-3' menjadi 'px-3 py-2' --}}
                            <input type="text" wire:model="form.title" id="title" placeholder="Contoh: Perbaikan Rutin Q3" 
                                class="w-full border rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500 transition duration-150 sm:text-sm
                                @error('form.title') border-red-500 placeholder-red-300 text-red-900 @else border-gray-300 text-gray-900 @enderror">
                                
                            @error('form.title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Deskripsi --}}
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Deskripsi Pekerjaan <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="form.description" id="description" rows="4" placeholder="Jelaskan detail pekerjaan, temuan, dan hasil..." 
                            class="w-full border rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500 transition duration-150
                            @error('form.description') border-red-500 placeholder-red-300 text-red-900 @else border-gray-300 text-gray-900 @enderror"></textarea>
                        @error('form.description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- BAGIAN 2: Detail Teknis --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="maintenance_type" class="block text-sm font-medium text-gray-700 mb-1">
                                Tipe Pemeliharaan <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="form.maintenance_type" id="maintenance_type" 
                                class="w-full border rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500 transition duration-150 bg-white
                                @error('form.maintenance_type') border-red-500 text-red-900 @else border-gray-300 text-gray-900 @enderror">
                                <option value="">-- Pilih Tipe --</option>
                                @foreach($maintenanceTypes as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                            @error('form.maintenance_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="execution_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Tanggal Pelaksanaan <span class="text-red-500">*</span>
                            </label>
                            <input type="date" wire:model="form.execution_date" id="execution_date" 
                                class="w-full border rounded-lg p-3 focus:ring-blue-500 focus:border-blue-500 transition duration-150
                                @error('form.execution_date') border-red-500 text-red-900 @else border-gray-300 text-gray-900 @enderror">
                            @error('form.execution_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- BAGIAN 3: Teknisi --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Tim Teknisi Bertugas <span class="text-red-500">*</span>
                        </label>
                        <div class="border rounded-xl p-4 max-h-48 overflow-y-auto bg-gray-50 
                            @error('form.selected_technicians') border-red-500 @else border-gray-300 @enderror">
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 md:gap-3">
                                @forelse($technicians as $technician)                              
                                    <label class="flex items-center space-x-3 py-1.5 px-2 md:p-2 rounded-lg hover:bg-white hover:shadow-sm transition cursor-pointer border border-transparent hover:border-gray-200">
                                        <input type="checkbox" wire:model="form.selected_technicians" value="{{ $technician->id }}" 
                                            class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300 transition duration-150 flex-shrink-0">
                                        <span class="text-sm text-gray-700 font-medium select-none truncate">{{ $technician->name }}</span>
                                    </label>
                                @empty
                                    <div class="col-span-full text-center py-4 text-gray-500">Data teknisi tidak tersedia.</div>
                                @endforelse
                            </div>
                        </div>
                        @error('form.selected_technicians') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- BAGIAN 4: UPLOAD FOTO --}}
                    <div class="mt-8">
                        <div class="flex justify-between items-end mb-3">
                            <label class="block text-sm font-medium text-gray-700">
                                Foto Dokumentasi <span class="text-red-500">*</span> 
                                <span class="hidden sm:inline text-gray-500 font-normal">(Maks 3 foto)</span>
                            </label>
                            
                            {{-- Indikator Jumlah Foto --}}
                            @php $photoCount = count($form->photos ?? []); @endphp
                            <span class="text-xs font-bold px-2 py-1 rounded {{ $photoCount >= 3 ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                                {{ $photoCount }} / 3
                            </span>
                        </div>

                        {{-- AREA TOMBOL UPLOAD (Responsive Grid) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 relative transition-all duration-300 {{ $photoCount >= 3 ? 'opacity-50 pointer-events-none grayscale' : '' }}">
                            
                            {{-- OPSI 1: BUKA KAMERA --}}
                            <button type="button" id="openCameraBtn" 
                                class="border-2 border-dashed border-blue-300 rounded-xl p-6 text-center bg-blue-50 hover:bg-blue-100 hover:border-blue-500 transition duration-200 cursor-pointer w-full">
                                <div class="flex flex-col items-center">
                                    <svg class="h-12 w-12 text-blue-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <p class="text-sm text-blue-700 font-semibold">Buka Kamera</p>
                                    <p class="text-xs text-blue-600 mt-1">Ambil Foto Langsung</p>
                                </div>
                            </button>

                            {{-- OPSI 2: PILIH FILE --}}
                            <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center bg-gray-50 hover:bg-green-50 hover:border-green-400 transition duration-200 relative cursor-pointer w-full">
                                <div class="flex flex-col items-center pointer-events-none">
                                    <svg class="h-12 w-12 text-green-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-sm text-green-700 font-semibold">Pilih File</p>
                                    <p class="text-xs text-green-600 mt-1">Dari Galeri</p>
                                </div>
                                <input type="file" 
                                    id="fileInput"
                                    wire:model="tempPhotos" 
                                    wire:key="upload-{{ $photoCount }}"
                                    multiple 
                                    accept="image/*"
                                    {{ $photoCount >= 3 ? 'disabled' : '' }} 
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer disabled:cursor-not-allowed">
                            </div>

                            {{-- Pesan Peringatan Tampil jika Penuh --}}
                            @if($photoCount >= 3)
                                <div class="absolute inset-0 flex items-center justify-center bg-white/60 z-10 backdrop-blur-[1px] rounded-xl">
                                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-2 rounded-lg shadow-sm text-sm font-semibold flex items-center">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                        Maksimal 3 Foto
                                    </div>
                                </div>
                            @endif
                        </div>

                        <p class="text-xs text-gray-500 mb-4">Format: JPG/PNG. Maksimal 10MB per foto.</p>

                        {{-- Loading --}}
                        <div wire:loading wire:target="tempPhotos" class="mb-4 w-full">
                            <div class="flex items-center text-blue-600 text-sm font-medium bg-blue-50 p-3 rounded-lg w-full">
                                <svg class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                Memproses gambar...
                            </div>
                        </div>
                        
                        @error('tempPhotos') <p class="mb-4 text-sm text-red-600 bg-red-50 p-3 rounded-lg border border-red-100">{{ $message }}</p> @enderror
                        @error('form.photos') <p class="mb-4 text-sm text-red-600 bg-red-50 p-3 rounded-lg">{{ $message }}</p> @enderror
                        @error('form.photos.*') <p class="mb-4 text-sm text-red-600 bg-red-50 p-3 rounded-lg">{{ $message }}</p> @enderror

                        {{-- Preview Gallery --}}
                        @if(!empty($form->photos))
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                @foreach($form->photos as $index => $photo)
                                    <div class="relative group rounded-xl overflow-hidden shadow-md border-2 border-gray-200 bg-gray-100">
                                        <img src="{{ $photo->temporaryUrl() }}" 
                                                alt="Preview {{ $index + 1 }}"
                                                class="w-full h-48 object-cover hover:scale-105 transition duration-300">
                                        
                                        <div class="absolute top-2 left-2 bg-blue-600 text-white text-xs font-bold px-2 py-1 rounded-full">
                                            #{{ $index + 1 }}
                                        </div>
                                        
                                        {{-- Tombol Hapus --}}
                                        <button type="button" wire:click="removePhoto({{ $index }})" 
                                            class="absolute top-2 right-2 bg-white/90 text-red-500 p-1.5 rounded-full hover:bg-red-500 hover:text-white shadow-lg transition-all duration-200 md:opacity-0 group-hover:opacity-100 cursor-pointer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- FOOTER --}}
                <div class="mt-10 pt-6 border-t border-gray-200 flex flex-col-reverse md:flex-row justify-end gap-3">
                    <a href="{{ route('admin.maintenances.index') }}" wire:navigate class="w-full md:w-auto text-center px-6 py-2.5 rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 font-medium transition duration-150 shadow-sm">
                        Batal
                    </a>

                    <button 
                        type="button"
                        {{-- 1. STATE: Default false --}}
                        x-data="{ loading: false }"
                        
                        {{-- 2. TRIGGER: Set loading true, lalu panggil Livewire --}}
                        x-on:click="loading = true; $wire.save()"
                        
                        {{-- 3. SAFETY: Jika validasi gagal, Livewire me-render ulang tombol ini. 
                            wire:key yang berubah memaksa elemen dibuat ulang, 
                            sehingga x-data di-reset kembali ke false --}}
                        wire:key="btn-simpan-{{ $errors->count() }}"
                        
                        :disabled="loading"
                        class="w-full md:w-auto flex justify-center items-center px-6 py-2.5 rounded-lg bg-blue-600 text-white font-medium shadow-md transition hover:bg-blue-700 disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        {{-- KONTEN NORMAL: Muncul saat loading false --}}
                        <div x-show="!loading" class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            <span>Simpan Data</span>
                        </div>

                        {{-- KONTEN LOADING: Muncul saat loading true --}}
                        <div x-show="loading" style="display: none;" class="flex items-center">
                            <svg class="animate-spin w-5 h-5 mr-2 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Memproses...</span>
                        </div>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL KAMERA (Responsive Margin & Width) --}}
    <div id="cameraModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full p-6 m-4 md:m-0">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">Ambil Foto</h3>
                <button type="button" id="closeCameraBtn" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="relative bg-black rounded-xl overflow-hidden" style="aspect-ratio: 4/3;">
                <video id="cameraVideo" autoplay playsinline class="w-full h-full object-cover"></video>
                <canvas id="cameraCanvas" class="hidden"></canvas>
            </div>
            
            <div class="mt-4 flex justify-center gap-3">
                <button type="button" id="captureBtn" class="w-full md:w-auto justify-center px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path></svg>
                    Ambil Foto
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let stream = null;
    const modal = document.getElementById('cameraModal');
    const video = document.getElementById('cameraVideo');
    const canvas = document.getElementById('cameraCanvas');
    const openBtn = document.getElementById('openCameraBtn');
    const closeBtn = document.getElementById('closeCameraBtn');
    const captureBtn = document.getElementById('captureBtn');

    // Buka kamera
    openBtn.addEventListener('click', async () => {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ 
                video: { facingMode: 'environment' } 
            });
            video.srcObject = stream;
            modal.classList.remove('hidden');
        } catch (err) {
            alert('Tidak dapat mengakses kamera. Pastikan izin kamera sudah diberikan.');
            console.error(err);
        }
    });

    // Tutup kamera
    closeBtn.addEventListener('click', () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
            stream = null;
        }
        modal.classList.add('hidden');
    });

    // Ambil foto
    captureBtn.addEventListener('click', () => {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);
        
        canvas.toBlob((blob) => {
            const file = new File([blob], `photo_${Date.now()}.jpg`, { type: 'image/jpeg' });
            const dataTransfer = new DataTransfer();
            dataTransfer.items.add(file);
            
            // Selector menggunakan ID spesifik yang saya tambahkan: #fileInput
            const fileInput = document.getElementById('fileInput');
            
            if(fileInput) {
                fileInput.files = dataTransfer.files;
                fileInput.dispatchEvent(new Event('change', { bubbles: true }));
            } else {
                console.error('Input file tidak ditemukan');
            }
            
            // Tutup modal
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
            }
            modal.classList.add('hidden');
        }, 'image/jpeg', 0.9);
    });
</script>