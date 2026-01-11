<div class="container mx-auto px-4 py-2">
    <div class="max-w-4xl mx-auto">
        
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Edit Aset</h1>
            </div>
            <a href="{{ route('admin.assets.index') }}" wire:navigate class="text-sm text-gray-600 hover:text-gray-900 font-medium flex items-center transition">
                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>
        
        <form wire:submit="save">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                
                <div class="p-6 space-y-8">

                    {{-- SECTION 1: IDENTITAS ASET --}}
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-2 mb-4">Identitas Aset</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            
                            {{-- MODEL (DROPDOWN DENGAN ERROR STATE) --}}
                            <div class="md:col-span-2" x-data="{ open: false }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Model / Perangkat <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <button type="button" @click="open = !open" 
                                        class="w-full bg-white border rounded-md px-3 py-2 flex items-center justify-between shadow-sm outline-none transition text-left sm:text-sm
                                        @error('form.asset_model_id') border-red-500 text-red-900 focus:ring-1 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @enderror">
                                            <span class="flex items-center gap-3 truncate">
                                                @if($selectedModelName)
                                                    @if($selectedModelImage)
                                                        <img src="{{ asset('storage/'.$selectedModelImage) }}" class="h-6 w-6 rounded-md object-cover bg-gray-50 border border-gray-200 shrink-0">
                                                    @endif
                                                    <span class="block truncate font-medium">{{ $selectedModelName }}</span>
                                                @else
                                                    <span class="block truncate text-gray-400">Pilih Model Aset...</span>
                                                @endif
                                            </span>
                                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </span>
                                    </button>
                                    
                                    {{-- Dropdown Body --}}
                                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                                        <div class="px-2 py-2 border-b border-gray-100 bg-gray-50">
                                            <input type="text" wire:model.live.debounce.300ms="searchModel" class="block w-full border border-gray-300 rounded-md px-3 py-1.5 leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari model..." autofocus>
                                        </div>
                                        <ul class="max-h-48 overflow-y-auto">
                                            @forelse($models as $model)
                                                @php $displayString = "{$model->category->name} - {$model->name} - ({$model->model_number})"; @endphp
                                                <li wire:click="selectOption('asset_model_id', {{ $model->id }}, '{{ $displayString }}', 'searchModel', '{{ $model->image }}'); open = false" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 group transition-colors">
                                                    <div class="flex items-center">
                                                        @if($model->image) <img src="{{ asset('storage/'.$model->image) }}" class="h-9 w-9 rounded-md object-cover bg-white border mr-3 shrink-0"> @endif
                                                        <div class="flex flex-col overflow-hidden">
                                                            <span class="font-medium block truncate text-sm"><span class="font-semibold">{{ $model->category->name }}</span> - {{ $model->name }} - <span class="opacity-75 font-normal">({{ $model->model_number }})</span></span>
                                                            <span class="text-xs text-gray-500 group-hover:text-blue-100 truncate">{{ $model->manufacturer->name ?? 'No Manufacturer' }}</span>
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                                <li class="text-gray-500 select-none relative py-2 pl-3 pr-9 italic text-center text-xs">Model tidak ditemukan.</li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                                @error('form.asset_model_id') <p class="mt-1 text-xs text-red-600 font-semibold flex items-center"><svg class="w-3 h-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>{{ $message }}</p> @enderror
                            </div>

                            {{-- Asset Tag (STRICT 10 DIGIT & REQUIRED) --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">
                                    Kode Aset (Tag) 
                                    {{-- Hapus tanda bintang merah jika ini edit, atau biarkan saja --}}
                                    <span class="text-gray-400 text-xs font-normal">(Tidak dapat diubah)</span>
                                </label>
                                
                                <input type="text" 
                                    wire:model="form.asset_tag" 
                                    readonly {{-- ATRIBUT KUNCI: Agar tidak bisa diketik --}}
                                    class="w-full px-3 py-2 border rounded-md text-sm transition shadow-sm 
                                            bg-gray-200 text-gray-500 cursor-not-allowed border-gray-300 focus:outline-none"
                                    {{-- CLASS TAMBAHAN: bg-gray-200 (abu-abu), cursor-not-allowed (ikon dilarang) --}}
                                >
                                
                                {{-- Error message bisa dihapus atau disembunyikan karena field ini readonly --}}
                            </div>

                            {{-- Serial Number --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Seri (SN)</label>
                                <input type="text" wire:model.blur="form.serial" placeholder="Contoh: SN-12345"
                                    class="w-full px-3 py-2 border rounded-md text-sm transition shadow-sm focus:outline-none focus:ring-1 focus:ring-blue-500
                                    @error('form.serial') border-red-500 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-blue-500 @enderror">
                                @error('form.serial') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 2: STATUS & LOKASI --}}
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-2 mb-4">Status & Penempatan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            
                            {{-- STATUS --}}
                            <div class="col-span-1" x-data="{ open: false }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Status Aset <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <button type="button" @click="open = !open" 
                                        class="w-full bg-white border rounded-md px-3 py-2 flex items-center justify-between shadow-sm outline-none transition text-left sm:text-sm
                                        @error('form.asset_status_id') border-red-500 text-red-900 focus:ring-1 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @enderror">
                                            @if($selectedStatusName)
                                                <span class="block truncate font-medium">{{ $selectedStatusName }}</span>
                                            @else
                                                <span class="block truncate text-gray-400">Pilih Status...</span>
                                            @endif
                                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </span>
                                    </button>
                                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                                        <div class="px-2 py-2 border-b border-gray-100 bg-gray-50">
                                            <input type="text" wire:model.live.debounce.300ms="searchStatus" class="block w-full border-gray-300 rounded-md px-3 py-1.5 leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari status..." autofocus>
                                        </div>
                                        <ul class="max-h-48 overflow-y-auto">
                                            @forelse($statuses as $st)
                                                <li wire:click="selectOption('asset_status_id', {{ $st->id }}, '{{ $st->name }}', 'searchStatus'); open = false" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 group transition-colors">
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

                            {{-- ASSIGNMENT --}}
                            <div class="col-span-1" x-data="{ open: false }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ditugaskan Ke Karyawan (Opsional)</label>
                                <div class="relative">
                                    <button type="button" @click="open = !open" class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 flex items-center justify-between shadow-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-left sm:text-sm">
                                            @if($selectedEmployeeName)
                                                <span class="block truncate text-gray-900">{{ $selectedEmployeeName }}</span>
                                            @else
                                                <span class="block truncate text-gray-400">- Belum Ditugaskan -</span>
                                            @endif
                                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </span>
                                    </button>
                                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                                        <div class="px-2 py-2 border-b border-gray-100 bg-gray-50">
                                            <input type="text" wire:model.live.debounce.300ms="searchEmployee" class="block w-full border border-gray-300 rounded-md px-3 py-1.5 leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari karyawan..." autofocus>
                                        </div>
                                        <ul class="max-h-48 overflow-y-auto">
                                            <li wire:click="selectOption('assigned_employee_id', '', null, 'searchEmployee'); open = false" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-red-50 text-red-600 group transition-colors border-b border-gray-100">
                                                <span class="font-medium block truncate text-xs">- Kosongkan (Batalkan penugasan) -</span>
                                            </li>
                                            @forelse($employees as $emp)
                                                <li wire:click="selectOption('assigned_employee_id', {{ $emp->id }}, '{{ $emp->name }}', 'searchEmployee'); open = false" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 group transition-colors">
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

                            {{-- LOKASI (PARENT & CHILD) --}}
                            <div class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-5">
                                {{-- GEDUNG --}}
                                <div x-data="{ open: false }">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Utama / Gedung <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <button type="button" @click="open = !open" 
                                            class="w-full bg-white border rounded-md px-3 py-2 flex items-center justify-between shadow-sm outline-none transition text-left sm:text-sm
                                            @error('form.location_id') border-red-500 text-red-900 focus:ring-1 focus:ring-red-500 focus:border-red-500 @else border-gray-300 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 @enderror">
                                                @if($selectedParentName)
                                                    <span class="block truncate text-gray-900">{{ $selectedParentName }}</span>
                                                @else
                                                    <span class="block truncate text-gray-400">Pilih Gedung...</span>
                                                @endif
                                                <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                </span>
                                        </button>
                                        <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                                            <div class="px-2 py-2 border-b border-gray-100 bg-gray-50">
                                                <input type="text" wire:model.live.debounce.300ms="searchParent" class="block w-full border border-gray-300 rounded-md px-3 py-1.5 leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari gedung..." autofocus>
                                            </div>
                                            <ul class="max-h-48 overflow-y-auto">
                                                @forelse($parentLocations as $parent)
                                                    <li wire:click="selectParentLocation({{ $parent->id }}, '{{ $parent->name }}'); open = false" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 group transition-colors">
                                                        <span class="font-normal block truncate">{{ $parent->name }}</span>
                                                    </li>
                                                @empty
                                                    <li class="text-gray-500 select-none relative py-2 pl-3 pr-9 italic text-center text-xs">Gedung tidak ditemukan.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                    @error('form.location_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                </div>

                                {{-- RUANGAN --}}
                                <div x-data="{ open: false }">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Detail Ruangan <span class="text-gray-400 font-normal text-xs">(Opsional)</span></label>
                                    <div class="relative">
                                        <button type="button" @if($selectedParentId) @click="open = !open" @endif @if(!$selectedParentId) disabled @endif
                                            class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 flex items-center justify-between shadow-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-left sm:text-sm disabled:bg-gray-100 disabled:cursor-not-allowed">
                                            @if(!$selectedParentId)
                                                <span class="block truncate text-gray-400">-- Pilih Gedung Dahulu --</span>
                                            @elseif($selectedChildName)
                                                <span class="block truncate text-gray-900">{{ $selectedChildName }}</span>
                                            @else
                                                <span class="block truncate text-gray-500">-- Umum / Lobby / Tidak Spesifik --</span>
                                            @endif
                                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </span>
                                        </button>
                                        <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                                            <div class="px-2 py-2 border-b border-gray-100 bg-gray-50">
                                                <input type="text" wire:model.live.debounce.300ms="searchChild" class="block w-full border border-gray-300 rounded-md px-3 py-1.5 leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari ruangan..." autofocus>
                                            </div>
                                            <ul class="max-h-48 overflow-y-auto">
                                                <li wire:click="selectChildLocation('', null); open = false" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-yellow-50 text-yellow-700 group transition-colors border-b border-gray-100">
                                                    <span class="font-medium block truncate text-xs">-- Set ke Umum / Lobby (Gedung ini) --</span>
                                                </li>
                                                @forelse($childLocations as $child)
                                                    <li wire:click="selectChildLocation({{ $child->id }}, '{{ $child->name }}'); open = false" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 group transition-colors">
                                                        <span class="font-normal block truncate">{{ $child->name }}</span>
                                                    </li>
                                                @empty
                                                    <li class="text-gray-500 select-none relative py-2 pl-3 pr-9 italic text-center text-xs">Ruangan tidak ditemukan.</li>
                                                @endforelse
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 3: KEUANGAN & GARANSI --}}
                    <div>
                        <h3 class="text-base font-semibold text-gray-900 border-b border-gray-100 pb-2 mb-4">Pembelian & Garansi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            
                            {{-- SUPPLIER --}}
                            <div class="md:col-span-3" x-data="{ open: false }">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Pemasok</label>
                                <div class="relative">
                                    <button type="button" @click="open = !open" class="w-full bg-white border border-gray-300 rounded-md px-3 py-2 flex items-center justify-between shadow-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500 outline-none transition text-left sm:text-sm">
                                            <span class="flex items-center gap-3 truncate">
                                                @if($selectedSupplierName)
                                                    @if($selectedSupplierImage)
                                                        <img src="{{ asset('storage/'.$selectedSupplierImage) }}" class="h-6 w-6 rounded-full object-cover bg-gray-50 border border-gray-200 shrink-0">
                                                    @endif
                                                    <span class="block truncate text-gray-900 font-medium">{{ $selectedSupplierName }}</span>
                                                @else
                                                    <span class="block truncate text-gray-400">Pilih Pemasok...</span>
                                                @endif
                                            </span>
                                            <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-2">
                                                <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                            </span>
                                    </button>
                                    <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                                        <div class="px-2 py-2 border-b border-gray-100 bg-gray-50">
                                            <input type="text" wire:model.live.debounce.300ms="searchSupplier" class="block w-full border border-gray-300 rounded-md px-3 py-1.5 leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" placeholder="Cari supplier..." autofocus>
                                        </div>
                                        <ul class="max-h-48 overflow-y-auto">
                                            @forelse($suppliers as $sup)
                                                <li wire:click="selectOption('supplier_id', {{ $sup->id }}, '{{ $sup->name }}', 'searchSupplier', '{{ $sup->image }}'); open = false" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 group transition-colors">
                                                    <div class="flex items-center">
                                                        @if($sup->image) <img src="{{ asset('storage/'.$sup->image) }}" class="h-6 w-6 rounded-full object-cover bg-white border mr-3 shrink-0"> @endif
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

                            {{-- Form Fields Keuangan --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Beli</label>
                                <input type="date" wire:model="form.purchase_date" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Harga Beli</label>
                                <div class="relative rounded-md shadow-sm">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        <span class="text-gray-500 sm:text-sm">Rp</span>
                                    </div>
                                    <input type="number" wire:model.blur="form.purchase_cost" placeholder="0" 
                                        class="w-full pl-10 px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 
                                        @error('form.purchase_cost') border-red-500 focus:border-red-500 @else border-gray-300 focus:border-blue-500 @enderror">
                                </div>
                                @error('form.purchase_cost') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">No. Order / PO</label>
                                <input type="text" wire:model="form.order_number" placeholder="Contoh: PO-2024-001" class="w-full px-3 py-2 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition shadow-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Durasi Garansi</label>
                                <div class="relative rounded-md shadow-sm">
                                    <input type="number" wire:model.blur="form.warranty_months" placeholder="0" min="0" 
                                        class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 
                                        @error('form.warranty_months') border-red-500 focus:border-red-500 @else border-gray-300 focus:border-blue-500 @enderror">
                                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                                        <span class="text-gray-500 sm:text-xs">Bulan</span>
                                    </div>
                                </div>
                                @error('form.warranty_months') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Habis Masa Pakai (EOL)</label>
                                <input type="date" wire:model.blur="form.eol_date" 
                                    class="w-full px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 
                                    @error('form.eol_date') border-red-500 text-red-900 @else border-gray-300 focus:border-blue-500 @enderror">
                                @error('form.eol_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    {{-- SECTION 4: GAMBAR --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Aset</label>
                        <div class="flex items-start space-x-4">
                            {{-- Preview Logic --}}
                            <div class="shrink-0">
                                @if ($form->image)
                                    <img src="{{ $form->image->temporaryUrl() }}" class="h-20 w-20 object-cover rounded-md border border-gray-200">
                                @elseif ($form->assetModel && $form->assetModel->image)
                                    <img src="{{ asset('storage/' . $form->assetModel->image) }}" class="h-20 w-20 object-cover rounded-md border border-gray-200">
                                @else
                                    <div class="h-20 w-20 rounded-md border border-gray-200 bg-gray-50 flex items-center justify-center text-gray-400">
                                        <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif
                            </div>

                            <div class="w-full">
                                <input type="file" wire:model="form.image" accept="image/png, image/jpeg, image/jpg"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer border rounded-md
                                    @error('form.image') border-red-500 text-red-900 @else border-gray-300 @enderror">
                                <p class="mt-1 text-xs text-gray-500">Maks 2MB. Format: jpeg, png, jpg.</p>
                                @error('form.image') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                <div wire:loading wire:target="form.image" class="mt-1 text-xs text-blue-600 font-medium">Mengupload gambar...</div>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- FOOTER ACTION --}}
                <div class="bg-gray-50 px-6 py-4 flex items-center justify-end space-x-3 border-t border-gray-200">
                    <a href="{{ route('admin.assets.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        Batal
                    </a>
                    <button type="button" 
                            {{-- PENTING: wire:key ini membuat tombol reset jika ada error validasi --}}
                            wire:key="btn-simpan-{{ $errors->count() }}"
                            x-data="{ loading: false }"
                            {{-- HAPUS .then(), biarkan loading true sampai halaman pindah --}}
                            x-on:click="loading = true; $wire.save()"
                            :disabled="loading"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 shadow-sm transition flex items-center justify-center disabled:opacity-75 disabled:cursor-not-allowed">
                        
                        {{-- TEKS: Hilang jika loading true --}}
                        <span x-show="!loading">Simpan Perubahan</span>

                        {{-- IKON: Muncul jika loading true --}}
                        <svg x-show="loading" style="display: none;" class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
                
            </div>
        </form>
    </div>
</div>