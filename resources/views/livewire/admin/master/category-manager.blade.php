<div class="container mx-auto px-4 ">

    <h1 class="text-2xl font-bold mb-6 text-gray-800">Kelola Kategori</h1>
    
    {{-- FLASH MESSAGE --}}
    @if (session()->has('message'))
        <div
            x-data="{ show: true }"
            x-init="setTimeout(() => show = false, 1500)"
            x-show="show"
            x-transition.opacity.duration.300ms
            x-cloak
            class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded shadow-sm"
        >
            {{ session('message') }}
        </div>
    @endif

    {{-- HEADER & SEARCH --}}
    <div class="flex flex-col sm:flex-row justify-between items-center mb-6 gap-4">
        <div class="relative w-full sm:flex-1 sm:max-w-xs">
            <input
                wire:model.live.debounce.300ms="search"
                type="text"
                placeholder="Cari kategori..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl
                    focus:outline-none focus:ring-2 focus:ring-blue-500
                    focus:border-blue-500 text-sm shadow-sm">

            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
        </div>

        <button
            wire:click="create"
            class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white
                font-semibold py-2.5 px-6 rounded-xl
                flex items-center justify-center shadow-lg active:scale-95">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span>Tambah Kategori</span>
        </button>
    </div>

    {{-- TABLE --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nama Kategori
                        </th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>

                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($categories as $cat)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $cat->name }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                <div class="flex justify-center space-x-2">
                                    <button wire:click="edit({{ $cat->id }})"
                                            class="text-green-600 hover:text-green-900 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/>
                                        </svg>
                                    </button>

                                    <button wire:click="confirmDelete({{ $cat->id }})"
                                            class="text-red-600 hover:text-red-900 transition">
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="h-5 w-5"
                                            viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 011 1v6a1 1 0 11-2 0V8a1 1 0 011-1z"
                                                clip-rule="evenodd"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-6 py-8 text-center text-gray-400 italic">
                                Data tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $categories->links() }}
        </div>
    </div>

    {{-- MODAL FORM --}}
    @if($showFormModal)
    <div wire:loading.remove wire:target="closeModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            {{-- Backdrop --}}
            <div wire:click="closeModal" class="fixed inset-0 bg-gray-900/60 transition-opacity"></div>

            {{-- Card Modal --}}
            <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all sm:max-w-lg w-full z-10 relative">
                {{-- HEADER --}}
                <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-800">
                        {{ $isEditMode ? 'Edit Kategori' : 'Tambah Kategori Baru' }}
                    </h3>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- FORM --}}
                <form wire:submit.prevent="store" class="p-6">
                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">
                                Nama Kategori <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   wire:model="name"
                                   autofocus
                                   class="w-full border border-gray-300 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-blue-500 outline-none shadow-sm transition ">
                            @error('name') 
                                <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> 
                            @enderror
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-3">
                        <button type="button" 
                                wire:click="closeModal"
                                class="px-6 py-2.5 bg-gray-100 rounded-xl font-bold hover:bg-gray-200 transition">
                            Batal
                        </button>
                        <button type="submit" 
                                wire:loading.attr="disabled"
                                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 flex items-center justify-center min-w-[120px] transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="store">Simpan</span>
                            <svg wire:loading wire:target="store" class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL DELETE --}}
    @if($showDeleteModal)
    <div wire:loading.remove wire:target="closeModal" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div wire:click="closeModal" class="fixed inset-0 bg-gray-900/60 transition-opacity"></div>
            
            <div class="bg-white rounded-2xl p-6 shadow-2xl transform transition-all sm:max-w-sm w-full z-10 relative text-center border border-gray-100">
                <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>

                <h3 class="text-xl font-bold text-gray-900">Hapus Data?</h3>
                <p class="text-gray-500 mt-2 text-sm leading-relaxed">
                    Apakah Anda yakin ingin menghapus kategori ini? <br>
                    <span class="text-red-500 font-medium">Tindakan ini tidak dapat dibatalkan.</span>
                </p>
                
                <div class="mt-8 flex gap-3">
                    <button type="button" 
                            wire:click="closeModal"
                            class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold transition hover:bg-gray-200">
                        Batal
                    </button>
                    
                    <button wire:click="delete" 
                            wire:loading.attr="disabled"
                            class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl font-bold flex items-center justify-center transition hover:bg-red-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                        <svg wire:loading wire:target="delete" class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
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
