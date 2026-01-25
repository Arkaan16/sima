<div class="container mx-auto px-4">
    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Pekerjaan Saya</h1>
            <p class="text-sm text-gray-500 mt-1">Daftar riwayat pemeliharaan yang Anda kerjakan.</p>
        </div>
        
        {{-- Tombol Tambah --}}
        <a href="{{ route('employee.maintenances.create') }}" wire:navigate class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg shadow-md flex justify-center items-center transition transform hover:scale-105">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
            </svg>
            Tambah Pemeliharaan
        </a>
    </div>

    {{-- Notifikasi --}}
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 1500)" x-show="show" x-transition class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm">
            <p>{{ session('success') }}</p>
        </div>
    @endif

    {{-- Filter Section --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-600 mb-1">Cari</label>
                <input wire:model.live.debounce.500ms="search" type="text" placeholder="Judul, Kode Aset, atau Deskripsi..." class="w-full border border-gray-300 rounded-lg p-2.5 focus:ring-blue-500 focus:border-blue-500 text-sm">
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
        
        <div class="md:hidden px-4 py-2 bg-blue-50 text-blue-600 text-xs flex items-center">
            <svg class="w-4 h-4 mr-1 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
            Geser tabel ke kiri untuk melihat aksi
        </div>

        <div class="overflow-x-auto relative">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Kode Aset</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Deskripsi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Teknisi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider whitespace-nowrap">Jenis</th>
                        <th class="sticky right-0 bg-gray-50 px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider z-10 shadow-l">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($maintenances as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $item->asset->asset_tag ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item->title }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ Str::limit($item->description, 30) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{-- Menampilkan list teknisi (User login paling depan) --}}
                                @if($item->technicians->isNotEmpty())
                                    <span class="font-semibold text-blue-700">{{ $item->technicians->first()->name }}</span>
                                    @if($item->technicians->count() > 1)
                                        <span class="text-xs text-gray-400">, +{{ $item->technicians->count() - 1 }} lainnya</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($item->execution_date)->translatedFormat('d M Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $item->badge_class }}">
                                    {{ $item->type_label }}
                                </span>
                            </td>

                            {{-- Sticky Action Column --}}
                            <td class="sticky right-0 bg-white px-4 py-4 whitespace-nowrap text-sm font-medium shadow-l">
                                <div class="flex items-center justify-center space-x-2">
                                    {{-- Tombol Detail (Biru) {{ route('employee.maintenances.show', $item->id) }}--}}
                                    <a href="{{ route('employee.maintenances.show', $item->id) }}" wire:navigate class="text-blue-600 hover:text-blue-900 p-1" title="Lihat Detail">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                        </svg>
                                    </a>

                                    {{-- Tombol Edit (Hijau) {{ route('employee.maintenances.edit', $item->id) }} --}}
                                    <a href="{{ route('employee.maintenances.edit', $item->id) }}" wire:navigate class="text-green-600 hover:text-green-900 p-1" title="Edit Laporan">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                Anda belum memiliki riwayat pekerjaan.
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
    
</div>