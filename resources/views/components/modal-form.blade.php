{{-- resources/views/components/modal-form.blade.php --}}

@props([
    'show' => false,
    'title' => 'Form',
    'maxWidth' => 'lg', // sm, md, lg, xl, 2xl
    'submitText' => 'Simpan',
    'submitAction' => 'store',
])

@php
$maxWidthClass = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

@if($show)
<div wire:loading.remove wire:target="closeModal" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        {{-- Backdrop --}}
        <div wire:click="closeModal" class="fixed inset-0 bg-gray-900/60 transition-opacity"></div>

        {{-- Card Modal --}}
        <div class="bg-white rounded-2xl overflow-hidden shadow-2xl transform transition-all {{ $maxWidthClass }} w-full z-10 relative">
            {{-- HEADER --}}
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                <h3 class="text-lg font-bold text-gray-800">
                    {{ $title }}
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- FORM --}}
            <form wire:submit.prevent="{{ $submitAction }}" class="p-6">
                <div class="space-y-5">
                    {{ $slot }}
                </div>

                {{-- FOOTER BUTTONS --}}
                <div class="mt-8 flex justify-end gap-3">
                    <button type="button" 
                            wire:click="closeModal"
                            class="px-6 py-2.5 bg-gray-100 rounded-xl font-bold hover:bg-gray-200 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            wire:loading.attr="disabled"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold shadow-lg shadow-blue-500/30 flex items-center justify-center min-w-[120px] transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="{{ $submitAction }}">{{ $submitText }}</span>
                        <svg wire:loading wire:target="{{ $submitAction }}" class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
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