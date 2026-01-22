<div 
    x-data="{ 
        showFormModal: false,
        showDeleteModal: false,
        
        // Helper Client Side untuk Dropdown
        setParent(id, name) {
            $wire.set('parent_location_id', id);
            $wire.set('selectedParentName', name);
            $wire.set('parentSearch', '');
        },
        clearParent() {
            $wire.set('parent_location_id', null);
            $wire.set('selectedParentName', '');
            $wire.set('parentSearch', '');
        }
    }"
    {{-- EVENT LISTENERS GLOBAL (ANTI CRASH) --}}
    x-on:open-modal-form.window="showFormModal = true; showDeleteModal = false"
    x-on:open-modal-delete.window="showDeleteModal = true; showFormModal = false"
    x-on:close-all-modals.window="showFormModal = false; showDeleteModal = false"
    class="container mx-auto px-4"
>

    <h1 class="text-2xl font-bold mb-6 text-gray-800">Kelola Lokasi & Ruangan</h1>
    
    {{-- FLASH MESSAGES --}}
    <div>
        @if (session()->has('message'))
            <div wire:key="alert-success" x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
                 class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex justify-between items-center">
                <span>{{ session('message') }}</span>
                <button @click="show = false" class="text-green-700 hover:text-green-900 font-bold">&times;</button>
            </div>
        @endif

        @if (session()->has('error'))
            <div wire:key="alert-error" x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
                 class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm flex justify-between items-center">
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="text-red-700 hover:text-red-900 font-bold">&times;</button>
            </div>
        @endif
    </div>

    {{-- HEADER & SEARCH --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="relative w-full sm:flex-1 sm:max-w-xs">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari gedung atau ruangan..."
                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <button wire:click="create"
                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-xl flex items-center justify-center shadow-lg active:scale-95 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <span>Tambah Data</span>
        </button>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Gedung / Ruangan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipe / Lokasi Induk</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($locations as $item)
                        {{-- WIRE:KEY SANGAT PENTING UNTUK MENCEGAH TABEL HILANG --}}
                        <tr wire:key="loc-{{ $item->id }}" class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">{{ $item->name }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->parent)
                                    <div class="flex flex-col">
                                        <span class="text-xs font-semibold text-gray-500 uppercase">Ruangan di:</span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 w-fit mt-1">
                                            {{ $item->parent->name }}
                                        </span>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-800 text-white">
                                        GEDUNG / UTAMA
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                <div class="flex justify-center space-x-2">
                                    <button wire:click="edit({{ $item->id }})" class="text-green-600 hover:text-green-900 transition p-1">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                    </button>

                                    <button wire:click="confirmDelete({{ $item->id }})" class="text-red-600 hover:text-red-900 transition p-1">
                                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 011 1v6a1 1 0 11-2 0V8a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-6 py-8 text-center text-gray-400 italic">Belum ada data lokasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200">{{ $locations->links() }}</div>
    </div>

    {{-- ================= MODALS ================= --}}

    {{-- MODAL FORM --}}
    {{-- wire:ignore.self PENTING agar display state dikelola penuh oleh Alpine --}}
    <div wire:ignore.self 
         x-show="showFormModal" x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="showFormModal = false"></div>

        <div class="bg-white rounded-2xl shadow-2xl transform transition-all w-full max-w-lg relative z-10 flex flex-col max-h-[90vh] overflow-hidden"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800" x-text="$wire.isEditMode ? 'Edit Data' : 'Tambah Data Baru'"></h3>
                <button @click="showFormModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>

            <div class="p-6 overflow-y-auto">
                <form wire:submit.prevent="store">
                    <div class="space-y-6">
                        
                        {{-- Nama Lokasi --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Gedung / Ruangan <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="name" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm">
                            @error('name') 
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        {{-- DROPDOWN PARENT --}}
                        <div x-data="{ open: false }">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Berada di Gedung? <span class="text-gray-400 font-normal">(Opsional)</span></label>
                            <p class="text-xs text-gray-500 mb-2">
                                <span class="font-bold text-gray-700">Tips:</span> Pilih Gedung jika Anda menginput Ruangan. Biarkan kosong jika Anda menginput Gedung baru.
                            </p>

                            <div class="relative">
                                <button type="button" @click="open = !open" class="w-full bg-white border border-gray-300 rounded-xl px-4 py-2.5 flex items-center justify-between shadow-sm focus:ring-2 focus:ring-blue-500 outline-none transition text-left">
                                    <template x-if="$wire.selectedParentName">
                                        <span class="block truncate text-gray-900 font-medium">
                                            <span class="text-gray-500 font-normal mr-1">Di dalam:</span> <span x-text="$wire.selectedParentName"></span>
                                        </span>
                                    </template>
                                    <template x-if="!$wire.selectedParentName">
                                        <span class="block truncate text-gray-400">-- Tidak Ada (Ini adalah Gedung Baru) --</span>
                                    </template>
                                    
                                    <div x-show="$wire.selectedParentName" @click.stop="clearParent()" class="mr-2 p-1 hover:bg-red-100 rounded-full text-gray-400 hover:text-red-500 transition cursor-pointer" title="Hapus Pilihan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </div>
                                    <span class="pointer-events-none flex items-center">
                                        <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </span>
                                </button>

                                <div x-show="open" @click.away="open = false" style="display: none;" class="absolute z-50 mt-1 w-full bg-white shadow-xl max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-hidden focus:outline-none sm:text-sm">
                                    
                                    <div class="px-2 py-2 border-b border-gray-100 bg-gray-50">
                                        <input type="text" wire:model.live.debounce.300ms="parentSearch" class="block w-full border border-gray-300 rounded-lg px-3 py-1.5 leading-5 bg-white placeholder-gray-500 focus:outline-none sm:text-sm" placeholder="Cari nama gedung..." autofocus>
                                    </div>
                                    
                                    <ul class="max-h-48 overflow-y-auto">
                                        <li @click="clearParent(); open = false" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-700 italic border-b border-gray-50">
                                            <span class="font-normal block truncate">-- Buat Sebagai Gedung Baru (Root) --</span>
                                        </li>
                                        @forelse($this->parents as $p)
                                            <li @click="setParent({{ $p->id }}, '{{ addslashes($p->name) }}'); open = false" 
                                                class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-blue-600 hover:text-white text-gray-900 group transition-colors">
                                                <div class="flex flex-col">
                                                    <span class="font-bold block truncate">{{ $p->name }}</span>
                                                    <span class="text-[10px] text-gray-400 group-hover:text-blue-200">Gedung Utama</span>
                                                </div>
                                            </li>
                                        @empty
                                            <li class="text-gray-500 select-none relative py-2 pl-3 pr-9 italic text-center text-xs">Gedung tidak ditemukan.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                            @error('parent_location_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showFormModal = false" class="px-6 py-2.5 bg-gray-100 rounded-xl font-bold text-gray-700 hover:bg-gray-200 transition">Batal</button>
                        
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="store"
                                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 flex items-center justify-center min-w-[120px] transition disabled:opacity-50">
                            <span>Simpan</span>
                            <div wire:loading wire:target="store" class="ml-2">
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            </div>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL DELETE --}}
    {{-- Z-INDEX DIBUAT TINGGI (z-60) UNTUK JAGA-JAGA AGAR SELALU DI ATAS FORM --}}
    <div wire:ignore.self 
         x-show="showDeleteModal" x-cloak 
         class="fixed inset-0 z-[60] flex items-center justify-center px-4"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        
        <div class="fixed inset-0 bg-gray-900/60" @click="showDeleteModal = false"></div>

        <div class="bg-white rounded-2xl p-6 shadow-2xl sm:max-w-sm w-full z-10 text-center transform transition-all"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            
            <div class="w-14 h-14 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            
            <h3 class="text-xl font-bold text-gray-900">Hapus Data?</h3>
            <p class="text-gray-500 mt-2 text-sm">Yakin hapus <span class="font-bold text-red-600" x-text="$wire.deleteName"></span>? <br><span class="text-red-500 font-medium">Data tidak bisa dikembalikan.</span></p>
            
            <div class="mt-6 flex gap-3">
                <button @click="showDeleteModal = false" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition">Batal</button>
                <button wire:click="delete" 
                        wire:loading.attr="disabled"
                        class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 flex items-center justify-center disabled:opacity-50">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <svg wire:loading wire:target="delete" class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </button>
            </div>
        </div>
    </div>

</div>