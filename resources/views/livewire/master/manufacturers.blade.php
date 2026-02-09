<div 
    x-data="{ 
        showFormModal: false,
        showDeleteModal: false,
    }"
    {{-- EVENT LISTENERS GLOBAL (Anti Crash) --}}
    x-on:open-modal-form.window="showFormModal = true; showDeleteModal = false"
    x-on:open-modal-delete.window="showDeleteModal = true; showFormModal = false"
    x-on:close-all-modals.window="showFormModal = false; showDeleteModal = false"
    class="container mx-auto px-4"
>

    <h1 class="text-2xl font-bold mb-6 text-gray-800">Kelola Pabrikan</h1>
    
    {{-- FLASH MESSAGES --}}
    <div>
        @if (session()->has('message'))
            <div wire:key="msg-success" x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
                 class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm flex justify-between items-center">
                <span>{{ session('message') }}</span>
                <button @click="show = false" class="text-green-700 hover:text-green-900 font-bold">&times;</button>
            </div>
        @endif

        @if (session()->has('error'))
            <div wire:key="msg-error" x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" 
                 class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm flex justify-between items-center">
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="text-red-700 hover:text-red-900 font-bold">&times;</button>
            </div>
        @endif
    </div>

    {{-- HEADER & SEARCH --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="relative w-full sm:flex-1 sm:max-w-xs">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari pabrikan..."
                   class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <button wire:click="create"
                class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-xl flex items-center justify-center shadow-lg active:scale-95 transition">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            <span>Tambah Pabrikan</span>
        </button>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">Logo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pabrikan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kontak</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tautan</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($manufacturers as $item)
                        <tr wire:key="man-{{ $item->id }}" class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->image)
                                    <img src="{{ asset('storage/'.$item->image) }}" class="h-12 w-12 rounded-lg object-contain border border-gray-200 bg-white" alt="Logo">
                                @else
                                    <div class="h-12 w-12 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400 text-xs font-bold">N/A</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">{{ $item->name }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-2 text-sm">
                                    @if($item->support_email) 
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-gray-700">{{ $item->support_email }}</span>
                                        </div>
                                    @endif
                                    @if($item->support_phone) 
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            <span class="text-gray-700">{{ $item->support_phone }}</span>
                                        </div>
                                    @endif
                                    @if(!$item->support_email && !$item->support_phone) 
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col gap-2 text-sm">
                                    @if($item->url) 
                                        <div class="flex items-start gap-2">
                                            <svg class="w-4 h-4 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                            </svg>
                                            <div class="flex flex-col">
                                                <span class="text-xs text-gray-500 font-medium mb-0.5">Website:</span>
                                                <a href="{{ $item->url }}" target="_blank" class="text-blue-600 hover:underline break-all" title="Buka Website">
                                                    {{ $item->url }}
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                    @if($item->support_url) 
                                        <div class="flex items-start gap-2">
                                            <svg class="w-4 h-4 text-purple-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/>
                                            </svg>
                                            <div class="flex flex-col">
                                                <span class="text-xs text-gray-500 font-medium mb-0.5">Support:</span>
                                                <a href="{{ $item->support_url }}" target="_blank" class="text-purple-600 hover:underline break-all" title="Buka Halaman Support">
                                                    {{ $item->support_url }}
                                                </a>
                                            </div>
                                        </div>
                                    @endif
                                    @if(!$item->url && !$item->support_url) 
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                <div class="flex justify-center space-x-2">
                                    {{-- EDIT: Panggil Server --}}
                                    <button wire:click="edit({{ $item->id }})" 
                                            class="text-green-600 hover:text-green-900 transition p-1"
                                            title="Edit">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                    </button>

                                    {{-- DELETE: Panggil Server --}}
                                    <button wire:click="confirmDelete({{ $item->id }})" 
                                            class="text-red-600 hover:text-red-900 transition p-1"
                                            title="Hapus">
                                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 011 1v6a1 1 0 11-2 0V8a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400 italic">Belum ada data pabrikan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200">{{ $manufacturers->links() }}</div>
    </div>

    {{-- MODAL FORM --}}
    <div wire:ignore.self x-show="showFormModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        
        <div class="fixed inset-0 bg-gray-900/60 transition-opacity" @click="showFormModal = false"></div>

        <div class="bg-white rounded-2xl shadow-2xl transform transition-all w-full max-w-2xl relative z-10 flex flex-col max-h-[90vh] overflow-hidden"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800" x-text="$wire.isEditMode ? 'Edit Pabrikan' : 'Tambah Pabrikan'"></h3>
                <button @click="showFormModal = false" class="text-gray-400 hover:text-gray-600"><svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg></button>
            </div>

            <div class="p-6 overflow-y-auto">
                <form wire:submit.prevent="store">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Nama --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Pabrikan <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="name" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm">
                            @error('name') 
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        {{-- URL Website --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">URL Website</label>
                            <input type="text" wire:model="url" placeholder="https://" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm">
                            @error('url') 
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        {{-- URL Support --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">URL Support</label>
                            <input type="text" wire:model="support_url" placeholder="https://" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm">
                            @error('support_url') 
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        {{-- Support Phone (STRICT INPUT) --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Support Phone</label>
                            <input type="tel" 
                                   wire:model="support_phone" 
                                   oninput="this.value = this.value.replace(/[^0-9+\-() ]/g, '')"
                                   placeholder="0812..." 
                                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm">
                            @error('support_phone') 
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        {{-- Support Email --}}
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Support Email</label>
                            <input type="email" wire:model="support_email" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm">
                            @error('support_email') 
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>

                        {{-- Upload Image --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                Logo Pabrikan <span class="text-xs font-normal text-gray-500 ml-1">(Format: JPG, JPEG, PNG | Maks 10MB)</span>
                            </label>
                            
                            <div class="flex items-center gap-4 p-3 border border-gray-200 rounded-xl bg-gray-50">
                                {{-- IMAGE PREVIEW LOGIC --}}
                                @if ($newImage)
                                    <img src="{{ $newImage->temporaryUrl() }}" class="w-16 h-16 rounded-lg object-cover border bg-white shadow-sm">
                                @elseif ($oldImage)
                                    <img src="{{ asset('storage/'.$oldImage) }}" class="w-16 h-16 rounded-lg object-cover border bg-white shadow-sm">
                                @else
                                    <div class="w-16 h-16 rounded-lg bg-gray-200 border flex items-center justify-center text-gray-400 text-xs font-bold">No Img</div>
                                @endif
                                
                                <div class="flex-1">
                                    <input type="file" 
                                           wire:model="newImage" 
                                           accept="image/png, image/jpeg, image/jpg"
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer">
                                    <div wire:loading wire:target="newImage" class="text-xs text-blue-500 mt-1 font-medium">Uploading...</div>
                                </div>
                            </div>
                            @error('newImage') 
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                            @enderror
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
    <div wire:ignore.self x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center px-4"
         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        
        <div class="fixed inset-0 bg-gray-900/60" @click="showDeleteModal = false"></div>

        <div class="bg-white rounded-2xl p-6 shadow-2xl sm:max-w-sm w-full z-10 text-center transform transition-all"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            
            <div class="w-14 h-14 bg-red-100 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            
            <h3 class="text-xl font-bold text-gray-900">Hapus Data?</h3>
            <p class="text-gray-500 mt-2 text-sm">Anda akan menghapus pabrikan <b x-text="$wire.deleteName"></b>. <br> <span class="text-red-500 font-medium">Tindakan ini tidak dapat dikembalikan.</span></p>
            
            <div class="mt-6 flex gap-3">
                <button @click="showDeleteModal = false" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition">Batal</button>
                <button wire:click="delete" 
                        wire:loading.attr="disabled"
                        class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 transition flex items-center justify-center disabled:opacity-50">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <svg wire:loading wire:target="delete" class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                </button>
            </div>
        </div>
    </div>
</div>