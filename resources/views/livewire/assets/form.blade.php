<div class="container mx-auto px-4">
    <div class="max-w-4xl mx-auto">
        
        {{-- Header Dinamis --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $pageTitle }}</h1>
            </div>
            <a href="{{ route('assets.index') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900 font-medium flex items-center transition">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>
        
        <form wire:submit="save" 
              x-data="{ isSaving: false }" 
              x-on:submit="isSaving = true">

            {{-- 
               FIX INFINITY LOADING: 
               Jika server mengembalikan error, kita inject script kecil ini.
               wire:key="{{ rand() }}" memaksa Livewire mengeksekusi ulang x-init 
               meskipun errornya sama persis dengan sebelumnya.
            --}}
            @if($errors->any())
                <div wire:key="{{ rand() }}" x-init="isSaving = false" class="hidden"></div>
                
                <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded-r-lg shadow-sm animate-pulse">
                    <p class="font-bold flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Gagal Menyimpan
                    </p>
                    <p class="text-sm mt-1">Mohon periksa kembali inputan yang bertanda merah.</p>
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6 space-y-8">

                    {{-- SECTION 1: IDENTITAS ASET --}}
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-2 mb-4">Identitas Aset</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            
                            {{-- MODEL (Live Search) --}}
                            <div class="md:col-span-2" 
                                x-data="{ 
                                    open: false, 
                                    selection: @entangle('selectedModelName'), 
                                    image: @entangle('selectedModelImage'),
                                    search: @entangle('searchModel').live
                                }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Model / Perangkat <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    
                                    {{-- TOMBOL TRIGGER DROPDOWN --}}
                                    <button type="button" @click="open = !open" class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 flex items-center justify-between shadow-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-left sm:text-sm">
                                        <span class="flex items-center gap-3 truncate">
                                            <template x-if="image">
                                                <img :src="'/storage/' + image" class="h-6 w-6 rounded-md object-cover bg-gray-50 border border-gray-200 shrink-0">
                                            </template>
                                            <span class="block truncate font-medium" :class="selection ? 'text-gray-900' : 'text-gray-400'" x-text="selection || 'Pilih Model Aset...'"></span>
                                        </span>
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </span>
                                    </button>
                                    
                                    {{-- ISI DROPDOWN --}}
                                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                                        
                                        {{-- SEARCH INPUT --}}
                                        <div class="px-2 py-2 border-b border-gray-100 bg-gray-50">
                                            <input type="text" x-model="search" class="block w-full border border-gray-300 rounded-md px-3 py-1.5 leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari model..." autofocus>
                                        </div>

                                        {{-- LIST ITEMS --}}
                                        <ul class="max-h-48 overflow-y-auto">
                                            @forelse($models as $model)
                                                @php 
                                                    // Format string tampilan: Kategori - Nama (Nomor)
                                                    $display = "{$model->category->name} - {$model->name} ({$model->model_number})"; 
                                                @endphp
                                                
                                                <li wire:key="model-{{ $model->id }}" 
                                                    wire:click="selectOption('asset_model_id', {{ $model->id }}, '{{ addslashes($display) }}', 'searchModel', '{{ $model->image }}')"
                                                    @click="open = false"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 group transition-colors">
                                                    
                                                    <div class="flex items-center">
                                                        {{-- GAMBAR --}}
                                                        @if($model->image) 
                                                            <img src="{{ asset('storage/'.$model->image) }}" class="h-8 w-8 rounded bg-white border mr-3 shrink-0 object-cover"> 
                                                        @else
                                                            {{-- Placeholder Gambar Kosong (Opsional, agar rapi jika tidak ada gambar) --}}
                                                            <div class="h-8 w-8 rounded bg-gray-100 border mr-3 shrink-0 flex items-center justify-center text-gray-400">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                            </div>
                                                        @endif

                                                        {{-- TEKS SEBARIS --}}
                                                        <div class="truncate text-sm">
                                                            <span class="font-medium">{{ $model->category->name }}</span>
                                                            <span class="mx-1 text-gray-400 group-hover:text-blue-200">-</span>
                                                            <span class="font-bold">{{ $model->name }}</span>
                                                            <span class="ml-1 text-gray-500 group-hover:text-blue-100 text-xs">({{ $model->model_number }})</span>
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="text-gray-500 select-none relative py-2 pl-3 pr-9 italic text-center text-xs">Model tidak ditemukan.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                                @error('form.asset_model_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Asset Tag --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Kode Aset (Tag) 
                                    @if($isEdit) <span class="text-gray-400 text-xs font-normal ml-1">(Tidak dapat diubah)</span> @endif
                                </label>
                                <input type="text" 
                                       wire:model="form.asset_tag" 
                                       placeholder="Auto-generate jika kosong" 
                                       @if($isEdit) readonly @endif
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm placeholder-gray-400 {{ $isEdit ? 'bg-gray-100 text-gray-500 cursor-not-allowed' : '' }}">
                                @error('form.asset_tag') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- Serial Number --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Seri (SN)</label>
                                <input type="text" wire:model="form.serial" placeholder="Contoh: SN-12345" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                                @error('form.serial') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 2: STATUS & LOKASI --}}
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-2 mb-4">Status & Penempatan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            
                            {{-- STATUS (Live Search) --}}
                            <div class="col-span-1" 
                                 x-data="{ 
                                    open: false, 
                                    selection: @entangle('selectedStatusName'),
                                    search: @entangle('searchStatus').live
                                 }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Aset <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <button type="button" @click="open = !open" class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 flex items-center justify-between shadow-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-left sm:text-sm">
                                        <span class="block truncate" :class="selection ? 'text-gray-900' : 'text-gray-400'" x-text="selection || 'Pilih Status...'"></span>
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </span>
                                    </button>
                                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                                        <div class="px-2 py-2 border-b border-gray-100 bg-gray-50">
                                            <input type="text" x-model="search" class="block w-full border border-gray-300 rounded-md px-3 py-1.5 leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari status..." autofocus>
                                        </div>
                                        <ul class="max-h-48 overflow-y-auto">
                                            @forelse($statuses as $st)
                                                <li wire:key="st-{{ $st->id }}" 
                                                    wire:click="selectOption('asset_status_id', {{ $st->id }}, '{{ $st->name }}', 'searchStatus')"
                                                    @click="open = false" 
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 group transition-colors">
                                                    <span class="font-normal block truncate">{{ $st->name }}</span>
                                                </li>
                                            @empty
                                                <li class="text-gray-500 select-none relative py-2 pl-3 pr-9 italic text-center text-xs">Status tidak ditemukan.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                                @error('form.asset_status_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- DITUGASKAN KE (Live Search) --}}
                            <div class="col-span-1" 
                                 x-data="{ 
                                    open: false, 
                                    selection: @entangle('selectedEmployeeName'),
                                    search: @entangle('searchEmployee').live
                                 }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ditugaskan Ke Karyawan (Opsional)</label>
                                <div class="relative">
                                    <button type="button" @click="open = !open" class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 flex items-center justify-between shadow-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-left sm:text-sm">
                                        <span class="block truncate" :class="selection ? 'text-gray-900' : 'text-gray-400'" x-text="selection || '- Belum Ditugaskan -'"></span>
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </span>
                                    </button>
                                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                                        <div class="px-2 py-2 border-b border-gray-100 bg-gray-50">
                                            <input type="text" x-model="search" class="block w-full border border-gray-300 rounded-md px-3 py-1.5 leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari karyawan..." autofocus>
                                        </div>
                                        <ul class="max-h-48 overflow-y-auto">
                                            <li wire:click="selectOption('assigned_employee_id', '', null, 'searchEmployee')" @click="open = false" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-red-50 text-red-600 group transition-colors border-b border-gray-100">
                                                <span class="font-medium block truncate text-xs">- Kosongkan (Batalkan penugasan) -</span>
                                            </li>
                                            @forelse($employees as $emp)
                                                <li wire:key="emp-{{ $emp->id }}" 
                                                    wire:click="selectOption('assigned_employee_id', {{ $emp->id }}, '{{ $emp->name }}', 'searchEmployee')"
                                                    @click="open = false"
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 group transition-colors">
                                                    <div class="flex flex-col">
                                                        <span class="font-normal block truncate">{{ $emp->name }}</span>
                                                        <span class="text-xs text-gray-500 group-hover:text-blue-200 truncate">{{ $emp->email }}</span>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="text-gray-500 select-none relative py-2 pl-3 pr-9 italic text-center text-xs">Karyawan tidak ditemukan.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            {{-- LOKASI (Gedung & Ruangan) --}}
                            <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5">
                                {{-- GEDUNG --}}
                                <div x-data="{ open: false, selection: @entangle('selectedParentName'), search: @entangle('searchParent').live }">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gedung / Lokasi Utama <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <button type="button" @click="open = !open" class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 flex items-center justify-between shadow-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-left sm:text-sm">
                                            <span class="block truncate" :class="selection ? 'text-gray-900' : 'text-gray-400'" x-text="selection || 'Pilih Gedung...'"></span>
                                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </span>
                                        </button>
                                        <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                                            <div class="px-2 py-2 border-b border-gray-100 bg-gray-50">
                                                <input type="text" x-model="search" class="block w-full border border-gray-300 rounded-md px-3 py-1.5 leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari gedung..." autofocus>
                                            </div>
                                            <ul class="max-h-48 overflow-y-auto">
                                                @forelse($parentLocations as $parent)
                                                    <li wire:key="parent-{{ $parent->id }}" 
                                                        wire:click="selectParentLocation({{ $parent->id }}, '{{ $parent->name }}')"
                                                        @click="open = false"
                                                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 group transition-colors">
                                                        <span class="font-normal block truncate">{{ $parent->name }}</span>
                                                    </li>
                                                @empty
                                                    <li class="text-gray-500 select-none relative py-2 pl-3 pr-9 italic text-center text-xs">Gedung tidak ditemukan.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                    @error('form.location_id') <p class="mt-1 text-xs text-red-600">Lokasi wajib dipilih.</p> @enderror
                                </div>

                                {{-- RUANGAN --}}
                                <div x-data="{ open: false, selection: @entangle('selectedChildName'), search: @entangle('searchChild').live }">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Detail Ruangan <span class="text-gray-400 font-normal text-xs">(Opsional)</span></label>
                                    <div class="relative">
                                        <button type="button" 
                                            @if($selectedParentId && $childLocations->count() > 0) @click="open = !open" @endif 
                                            @if(!$selectedParentId || $childLocations->isEmpty()) disabled @endif
                                            class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 flex items-center justify-between shadow-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-left sm:text-sm disabled:bg-gray-50 disabled:cursor-not-allowed">
                                            
                                            @if(!$selectedParentId)
                                                <span class="block truncate text-gray-400">-- Pilih Gedung Dahulu --</span>
                                            @elseif($childLocations->isEmpty())
                                                <span class="block truncate text-gray-400">Tidak ada ruangan (Default Gedung)</span>
                                            @else
                                                <span class="block truncate" :class="selection ? 'text-gray-900' : 'text-gray-500'" x-text="selection || 'Umum / Lobby / Tidak Spesifik'"></span>
                                            @endif

                                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </span>
                                        </button>
                                        
                                        <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                                            <div class="px-2 py-2 border-b border-gray-100 bg-gray-50">
                                                <input type="text" x-model="search" class="block w-full border border-gray-300 rounded-md px-3 py-1.5 leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari ruangan..." autofocus>
                                            </div>
                                            <ul class="max-h-48 overflow-y-auto">
                                                <li wire:click="selectChildLocation('', null)" @click="open = false" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-yellow-50 text-yellow-700 group transition-colors border-b border-gray-100">
                                                    <span class="font-medium block truncate text-xs">-- Set ke Umum / Lobby (Gedung ini) --</span>
                                                </li>
                                                @forelse($childLocations as $child)
                                                    <li wire:key="child-{{ $child->id }}" 
                                                        wire:click="selectChildLocation({{ $child->id }}, '{{ $child->name }}')"
                                                        @click="open = false" 
                                                        class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 group transition-colors">
                                                        <span class="font-normal block truncate">{{ $child->name }}</span>
                                                    </li>
                                                @empty
                                                    <li class="text-gray-500 select-none relative py-2 pl-3 pr-9 italic text-center text-xs">Ruangan tidak ditemukan.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                    @if($selectedParentId && !$selectedChildId)
                                        <p class="mt-1 text-xs text-blue-600">Aset akan tercatat di Lokasi Utama (Umum).</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 3: PEMBELIAN --}}
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-2 mb-4">Pembelian & Garansi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            
                            {{-- PEMASOK --}}
                            <div class="md:col-span-3" 
                                 x-data="{ open: false, selection: @entangle('selectedSupplierName'), image: @entangle('selectedSupplierImage'), search: @entangle('searchSupplier').live }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pemasok</label>
                                <div class="relative">
                                    <button type="button" @click="open = !open" class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 flex items-center justify-between shadow-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-left sm:text-sm">
                                        <span class="flex items-center gap-3 truncate">
                                            <template x-if="image">
                                                <img :src="'/storage/' + image" class="h-6 w-6 rounded-full object-cover bg-gray-50 border border-gray-200 shrink-0">
                                            </template>
                                            <span class="block truncate font-medium" :class="selection ? 'text-gray-900' : 'text-gray-400'" x-text="selection || 'Pilih Pemasok...'"></span>
                                        </span>
                                        <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        </span>
                                    </button>
                                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                                        <div class="px-2 py-2 border-b border-gray-100 bg-gray-50">
                                            <input type="text" x-model="search" class="block w-full border border-gray-300 rounded-md px-3 py-1.5 leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari supplier..." autofocus>
                                        </div>
                                        <ul class="max-h-48 overflow-y-auto">
                                            @forelse($suppliers as $sup)
                                                <li wire:key="sup-{{ $sup->id }}" 
                                                    wire:click="selectOption('supplier_id', {{ $sup->id }}, '{{ $sup->name }}', 'searchSupplier', '{{ $sup->image }}')"
                                                    @click="open = false" 
                                                    class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 group transition-colors">
                                                    <div class="flex items-center gap-2">
                                                        @if($sup->image) <img src="{{ asset('storage/'.$sup->image) }}" class="h-5 w-5 rounded-full object-cover"> @endif
                                                        <span class="font-normal block truncate">{{ $sup->name }}</span>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="text-gray-500 select-none relative py-2 pl-3 pr-9 italic text-center text-xs">Supplier tidak ditemukan.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            {{-- Tanggal Beli --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Beli</label>
                                <input type="date" wire:model="form.purchase_date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                            </div>

                            {{-- Harga Beli --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Beli</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">Rp</span>
                                    </div>
                                    <input type="number" wire:model="form.purchase_cost" placeholder="Contoh: 500000" class="w-full pl-10 px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition placeholder-gray-400 italic">
                                </div>
                            </div>

                            {{-- No Order --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. Order / PO</label>
                                <input type="text" wire:model="form.order_number" placeholder="Contoh: PO-2024-001" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                            </div>

                            {{-- Garansi --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Durasi Garansi</label>
                                <div class="relative rounded-md shadow-sm">
                                    <input type="number" wire:model="form.warranty_months" placeholder="0" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition">
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-gray-500 sm:text-xs">Bulan</span>
                                    </div>
                                </div>
                            </div>

                            {{-- EOL --}}
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Habis Masa Pakai (EOL)</label>
                                <input type="date" wire:model="form.eol_date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                                @error('form.eol_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 4: GAMBAR --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Aset</label>
                        <div class="flex items-start space-x-4">
                            <div class="shrink-0">
                                {{-- LOGIKA PREVIEW IMAGE (CREATE & EDIT) --}}
                                @if ($form->image)
                                    <img src="{{ $form->image->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-md border border-gray-200">
                                @elseif ($isEdit && $form->assetModel && $form->assetModel->image)
                                    <img src="{{ asset('storage/' . $form->assetModel->image) }}" class="h-20 w-20 object-cover rounded-md border border-gray-200">
                                @else
                                    <div class="h-20 w-20 rounded-md border border-gray-200 bg-gray-50 flex items-center justify-center text-gray-400">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                            </div>

                            <div class="w-full">
                                <input type="file" wire:model="form.image" accept="image/png, image/jpeg, image/jpg"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border border-gray-300 rounded-md">
                                <p class="mt-1 text-xs text-gray-500">Maks 10MB (jpeg, jpg/png). @if($isEdit) Biarkan kosong jika tidak ingin mengubah gambar. @endif</p>
                                @error('form.image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                <div wire:loading wire:target="form.image" class="mt-1 text-xs text-blue-600 font-medium">Mengupload gambar...</div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- FOOTER ACTION --}}
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 border-t border-gray-200">
                    
                    {{-- TOMBOL BATAL --}}
                    <a href="{{ route('assets.index') }}" 
                       {{-- Disable link saat saving agar tidak diklik --}}
                       :class="{ 'pointer-events-none opacity-50': isSaving }"
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                        Batal
                    </a>

                    {{-- TOMBOL SIMPAN --}}
                    <button type="submit" 
                        {{-- Disable tombol via Alpine saat isSaving true --}}
                        :disabled="isSaving"
                        class="min-w-[160px] px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition-all duration-200 disabled:opacity-75 disabled:cursor-not-allowed flex justify-center items-center">
                        
                        {{-- 
                            STATE 1: NORMAL 
                            Muncul saat isSaving = false
                        --}}
                        <span x-show="!isSaving" class="flex items-center">
                            @if($isEdit)                    
                                Simpan Perubahan
                            @else
                                Simpan Aset
                            @endif
                        </span>
                        
                        {{-- 
                            STATE 2: LOADING 
                            Muncul saat isSaving = true.
                            Menggunakan 'flex' agar ikon dan teks sejajar rapi.
                        --}}
                        <span x-show="isSaving" class="flex items-center gap-2" style="display: none;">
                            <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>Menyimpan...</span>
                        </span>

                    </button>
                </div>
                
            </div>
        </form>
    </div>
</div>