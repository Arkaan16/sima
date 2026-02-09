<div class="container mx-auto px-4">
    
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                {{ $isAdmin ? 'Kelola Pemeliharaan' : 'Pekerjaan Saya' }}
            </h1>
            @if(!$isAdmin)
                <p class="text-sm text-gray-500 mt-1">Daftar riwayat pemeliharaan yang Anda kerjakan.</p>
            @endif
        </div>
        
        {{-- Create Button (Semua Boleh Create sesuai rules awal) --}}
        <a href="{{route('maintenances.create')}}" wire:navigate class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg shadow-md flex justify-center items-center transition transform hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Tambah Data
        </a>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filter Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-600 mb-1">Cari</label>
                <input wire:model.live.debounce.500ms="search" type="text" placeholder="Judul, Kode Aset, Deskripsi, Karyawan..." class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Jenis</label>
                <select wire:model.live="type" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 text-sm">
                    <option value="">Semua</option>
                    @foreach($types as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600 mb-1">Tanggal</label>
                <input wire:model.live="date" type="date" class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 text-sm">
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Kode Aset</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Deskripsi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Karyawan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Jenis</th>
                        <th class="sticky right-0 bg-gray-50 px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider shadow-l">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($maintenances as $item)
                        <tr class="hover:bg-gray-50">
                            {{-- Asset Code --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $item->asset->asset_tag ?? '-' }}
                            </td>
                            
                            {{-- Title --}}
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900 font-medium">{{ $item->title }}</div>
                            </td>

                            {{-- Description (New Separate Column) --}}
                            <td class="px-6 py-4">
                                <div class="text-xs text-gray-500 line-clamp-2 min-w-[200px]" title="{{ $item->description }}">
                                    {{ Str::limit($item->description, 50) }}
                                </div>
                            </td>

                            {{-- Technicians --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($item->technicians->isNotEmpty())
                                    @php 
                                        $firstTech = $item->technicians->first();
                                        $isMe = !$isAdmin && $firstTech->id === auth()->id();
                                    @endphp
                                    <span class="{{ $isMe ? 'font-bold text-blue-700' : '' }}">
                                        {{ $firstTech->name }}
                                    </span>
                                    @if($item->technicians->count() > 1)
                                        <span class="text-xs text-gray-400">, +{{ $item->technicians->count() - 1 }}</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>

                            {{-- Date --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($item->execution_date)->translatedFormat('d M Y') }}
                            </td>

                            {{-- Badge --}}
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->badge_class }}">
                                    {{ $item->type_label }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="sticky right-0 bg-white px-4 py-4 whitespace-nowrap text-sm font-medium shadow-l">
                                <div class="flex items-center justify-center space-x-2">
                                    {{-- Show --}}
                                    <a href="{{route('maintenances.show', $item->id)}}" wire:navigate class="text-blue-600 hover:text-blue-900 p-1" title="Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                        </svg>
                                    </a>

                                    {{-- Edit --}}
                                    @can('update', $item)
                                    <a href="{{route('maintenances.edit', $item->id)}}" wire:navigate class="text-green-600 hover:text-green-900 p-1" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                    </a>
                                    @endcan

                                    {{-- Delete --}}
                                    @can('delete', $item)
                                    <button wire:click="confirmDelete({{ $item->id }})" class="text-red-600 hover:text-red-900 p-1" title="Hapus">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 011 1v6a1 1 0 11-2 0V8a1 1 0 011-1z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500 italic">
                                Belum ada data pemeliharaan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-white px-4 py-3 border-t border-gray-200">
            {{ $maintenances->links() }}
        </div>
    </div>

    {{-- MODAL DELETE (Hanya dirender jika user admin/boleh delete untuk hemat DOM) --}}
    @if($isAdmin && $showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center px-4">
            <div wire:click="closeModal" class="fixed inset-0 bg-gray-900/60 transition-opacity"></div>
            <div class="bg-white rounded-2xl p-6 shadow-2xl transform transition-all sm:max-w-sm w-full z-10 relative text-center">
                <div class="w-16 h-16 bg-red-50 text-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Hapus Data?</h3>
                <p class="text-gray-500 mt-2 text-sm">Apakah Anda yakin? <span class="text-red-500 font-medium">Data akan dihapus permanen.</span></p>
                <div class="mt-8 flex gap-3">
                    <button wire:click="closeModal" class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200">Batal</button>
                    <button wire:click="delete" wire:loading.attr="disabled" class="flex-1 px-4 py-2.5 bg-red-600 text-white rounded-xl font-bold hover:bg-red-700 flex items-center justify-center disabled:opacity-50">
                        <span wire:loading.remove wire:target="delete">Ya, Hapus</span>
                        <svg wire:loading wire:target="delete" class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>