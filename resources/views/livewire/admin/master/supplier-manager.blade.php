<div class="container mx-auto px-4">

    <h1 class="text-2xl font-bold mb-6 text-gray-800">Kelola Pemasok</h1>
    
    {{-- FLASH MESSAGES --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
            class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition
            class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- HEADER & SEARCH --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="relative w-full sm:flex-1 sm:max-w-xs">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari pemasok, narahubung..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm shadow-sm">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <button wire:click="create"
            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-6 rounded-xl flex items-center justify-center shadow-lg active:scale-95">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>Tambah Pemasok</span>
        </button>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        {{-- KOLOM GAMBAR --}}
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-20">
                            Logo
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Nama Pemasok
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Narahubung
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Kontak
                        </th>
                        {{-- KOLOM URL (DIPISAH) --}}
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Situs Web
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Alamat
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($suppliers as $item)
                        <tr class="hover:bg-gray-50 transition">
                            
                            {{-- LOGO --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->image)
                                    <img src="{{ asset('storage/'.$item->image) }}" 
                                         class="h-12 w-12 rounded-lg object-contain border border-gray-200 bg-white" 
                                         alt="Logo Pemasok">
                                @else
                                    <div class="h-12 w-12 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-400 text-xs font-bold">
                                        N/A
                                    </div>
                                @endif
                            </td>

                            {{-- NAMA PEMASOK --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-medium text-gray-900">{{ $item->name }}</span>
                            </td>

                            {{-- NARAHUBUNG --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $item->contact_name ?? '-' }}
                            </td>

                            {{-- KONTAK --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-1">
                                    @if($item->email)
                                        <div class="flex items-center text-xs text-gray-600" title="Email">
                                            <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            {{ $item->email }}
                                        </div>
                                    @endif
                                    @if($item->phone)
                                        <div class="flex items-center text-xs text-gray-600" title="Telepon">
                                            <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                            {{ $item->phone }}
                                        </div>
                                    @endif
                                    @if(!$item->email && !$item->phone) - @endif
                                </div>
                            </td>

                            {{-- SITUS WEB (Menampilkan Detail URL) --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($item->url)
                                    <a href="{{ $item->url }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-800 hover:underline block max-w-[200px] truncate" title="{{ $item->url }}">
                                        {{ $item->url }}
                                    </a>
                                @else
                                    <span class="text-gray-400 text-xs italic">-</span>
                                @endif
                            </td>

                            {{-- ALAMAT --}}
                            <td class="px-6 py-4">
                                <div class="text-xs text-gray-600 max-w-[180px] truncate" title="{{ $item->address }}">
                                    {{ $item->address ?? '-' }}
                                </div>
                            </td>

                            {{-- AKSI --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                <div class="flex justify-center space-x-2">
                                    <button wire:click="edit({{ $item->id }})" class="text-green-600 hover:text-green-900 transition" title="Ubah">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="confirmDelete({{ $item->id }})" class="text-red-600 hover:text-red-900 transition" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 011 1v6a1 1 0 11-2 0V8a1 1 0 011-1z" clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-400 italic">
                                Data pemasok tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $suppliers->links() }}
        </div>
    </div>

    {{-- MODAL FORM --}}
    @if($showFormModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6">
        <div wire:click="closeModal" class="fixed inset-0 bg-gray-900/60 transition-opacity" aria-hidden="true"></div>
        <div class="bg-white rounded-2xl shadow-2xl transform transition-all w-full max-w-2xl relative z-10 flex flex-col max-h-[90vh] overflow-hidden">
            
            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-gray-800">
                    {{ $isEditMode ? 'Ubah Data Pemasok' : 'Tambah Pemasok Baru' }}
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6 overflow-y-auto">
                <form wire:submit.prevent="store">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        {{-- Nama Pemasok --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nama Pemasok <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="name" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm" placeholder="Contoh: PT. Sumber Jaya Abadi">
                            @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Narahubung --}}
                        <div class="col-span-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Narahubung (Contact Person)</label>
                            <input type="text" wire:model="contact_name" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm" placeholder="Nama perwakilan...">
                        </div>

                        {{-- No HP --}}
                        <div class="col-span-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Nomor Telepon</label>
                            <input type="text" wire:model="phone" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm" placeholder="Contoh: 0812...">
                            @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Email --}}
                        <div class="col-span-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Alamat Email</label>
                            <input type="email" wire:model="email" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm" placeholder="email@contoh.com">
                            @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- URL Website --}}
                        <div class="col-span-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Situs Web (URL)</label>
                            <input type="text" wire:model="url" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm" placeholder="https://...">
                            @error('url') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        {{-- Alamat --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">Alamat Lengkap</label>
                            <textarea wire:model="address" rows="2" class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none transition shadow-sm" placeholder="Masukkan alamat lengkap..."></textarea>
                        </div>

                        {{-- Upload Gambar (FORM STYLE SESUAI ASSET MODEL) --}}
                        <div class="col-span-1 md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                Logo Pemasok
                                <span class="text-xs font-normal text-gray-500 ml-1">(Format: JPG, JPEG, PNG | Maks: 2MB)</span>
                            </label>
                            
                            <div class="flex items-center gap-4 p-3 border border-gray-200 rounded-xl bg-gray-50">
                                @if ($newImage)
                                    {{-- Preview Gambar Baru --}}
                                    <img src="{{ $newImage->temporaryUrl() }}" class="w-16 h-16 rounded-lg object-cover border bg-white shadow-sm">
                                @elseif ($oldImage)
                                    {{-- Preview Gambar Lama --}}
                                    <img src="{{ asset('storage/'.$oldImage) }}" class="w-16 h-16 rounded-lg object-cover border bg-white shadow-sm">
                                @else
                                    {{-- Placeholder Kosong --}}
                                    <div class="w-16 h-16 rounded-lg bg-gray-200 border flex items-center justify-center text-gray-400 text-xs font-bold">Tidak Ada</div>
                                @endif
                                
                                <div class="flex-1">
                                    <input type="file" 
                                           wire:model="newImage" 
                                           accept="image/png, image/jpeg, image/jpg"
                                           class="block w-full text-sm text-gray-500 
                                                  file:mr-4 file:py-2 file:px-4 
                                                  file:rounded-full file:border-0 
                                                  file:text-xs file:font-semibold 
                                                  file:bg-blue-600 file:text-white 
                                                  hover:file:bg-blue-700 cursor-pointer">
                                    
                                    <div wire:loading wire:target="newImage" class="text-xs text-blue-500 mt-1 font-medium">Sedang mengunggah...</div>
                                </div>
                            </div>
                            @error('newImage') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                        </div>

                    </div>

                    {{-- Footer Buttons --}}
                    <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" wire:click="closeModal" class="px-6 py-2.5 bg-gray-100 rounded-xl font-bold text-gray-700 hover:bg-gray-200 transition">Batal</button>
                        
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                wire:target="store, newImage" 
                                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 flex items-center justify-center min-w-[120px] transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <span wire:loading.remove wire:target="store">Simpan</span>
                            <svg wire:loading wire:target="store" class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL DELETE --}}
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
        <div wire:click="closeModal" class="fixed inset-0 bg-gray-900/60 transition-opacity"></div>
        <div class="bg-white rounded-2xl p-6 shadow-2xl transform transition-all sm:max-w-sm w-full z-10 relative text-center">
            <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900">Hapus Data?</h3>
            <p class="text-gray-500 mt-2 text-sm">Apakah Anda yakin? <span class="text-red-500 font-medium">Data ini tidak dapat dikembalikan setelah dihapus.</span></p>
            
            <div class="mt-8 flex gap-3">
                <button wire:click="closeModal" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200">Batal</button>
                <button wire:click="delete" 
                        wire:loading.attr="disabled"
                        class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 flex items-center justify-center disabled:opacity-50">
                    <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                    <svg wire:loading wire:target="delete" class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>