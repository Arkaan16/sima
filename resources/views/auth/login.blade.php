<x-guest-layout>
    <x-slot name="title">
        Login
    </x-slot>
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        
        <div class="text-center mb-6">
            <img src="/images/ptba.png" alt="PTBA Logo" class="w-40 sm:w-48 mx-auto mb-4">
            
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">SIMA-PTBA</h1>
            <p class="text-gray-500 text-sm sm:text-base mt-1">
                {{ __('Silakan masuk untuk melanjutkan') }}
            </p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                    {{ __('Email') }}
                </label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 text-gray-700 placeholder-gray-400 shadow-sm"
                    placeholder="nama@contoh.com"
                >
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                    {{ __('Kata Sandi') }}
                </label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 text-gray-700 placeholder-gray-400 shadow-sm"
                    placeholder="••••••••"
                >
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex justify-end">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium hover:underline">
                        {{ __('Lupa kata sandi?') }}
                    </a>
                @endif
            </div>

            <button
                type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
                {{ __('Log in') }}
            </button>
        </form>

        <p class="text-center text-gray-500 text-xs sm:text-sm mt-6">
            © {{ date('Y') }} PT Bukit Asam Tbk. All rights reserved.
        </p>
    </div>
</x-guest-layout>