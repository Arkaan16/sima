{{-- resources/views/components/modal-confirm.blade.php --}}

@props([
    'show' => false,
    'title' => 'Konfirmasi',
    'message' => 'Apakah Anda yakin?',
    'confirmText' => 'Ya, Lanjutkan',
    'cancelText' => 'Batal',
    'confirmAction' => 'confirm',
    'type' => 'danger', // danger, warning, info, success
])

@php
$colors = [
    'danger' => [
        'bg' => 'bg-red-50',
        'text' => 'text-red-500',
        'button' => 'bg-red-600 hover:bg-red-700',
    ],
    'warning' => [
        'bg' => 'bg-yellow-50',
        'text' => 'text-yellow-500',
        'button' => 'bg-yellow-600 hover:bg-yellow-700',
    ],
    'info' => [
        'bg' => 'bg-blue-50',
        'text' => 'text-blue-500',
        'button' => 'bg-blue-600 hover:bg-blue-700',
    ],
    'success' => [
        'bg' => 'bg-green-50',
        'text' => 'text-green-500',
        'button' => 'bg-green-600 hover:bg-green-700',
    ],
][$type];
@endphp

@if($show)
<div wire:loading.remove wire:target="closeModal" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        {{-- Backdrop --}}
        <div wire:click="closeModal" class="fixed inset-0 bg-gray-900/60 transition-opacity"></div>
        
        {{-- Modal Content --}}
        <div class="bg-white rounded-2xl p-6 shadow-2xl transform transition-all sm:max-w-sm w-full z-10 relative text-center border border-gray-100">
            {{-- Icon --}}
            <div class="w-16 h-16 {{ $colors['bg'] }} {{ $colors['text'] }} rounded-full flex items-center justify-center mx-auto mb-4">
                @if($type === 'danger')
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                @elseif($type === 'warning')
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                @elseif($type === 'info')
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                @else
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                @endif
            </div>

            {{-- Title --}}
            <h3 class="text-xl font-bold text-gray-900">{{ $title }}</h3>
            
            {{-- Message --}}
            <div class="text-gray-500 mt-2 text-sm leading-relaxed">
                {!! $message !!}
            </div>
            
            {{-- Buttons --}}
            <div class="mt-8 flex gap-3">
                <button type="button" 
                        wire:click="closeModal"
                        class="flex-1 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-bold transition hover:bg-gray-200">
                    {{ $cancelText }}
                </button>
                
                <button wire:click="{{ $confirmAction }}" 
                        wire:loading.attr="disabled"
                        class="flex-1 px-4 py-2.5 {{ $colors['button'] }} text-white rounded-xl font-bold flex items-center justify-center transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="{{ $confirmAction }}">{{ $confirmText }}</span>
                    <svg wire:loading wire:target="{{ $confirmAction }}" class="animate-spin h-5 w-5 text-white" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endif