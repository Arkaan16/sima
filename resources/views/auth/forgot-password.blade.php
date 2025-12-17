<x-guest-layout>
    <x-slot name="title">
        Lupa Password
    </x-slot>
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        
        <div class="text-center mb-6">
            <img src="{{ asset('images/ptba.png') }}" alt="PTBA Logo" class="w-40 sm:w-48 mx-auto mb-4">
            
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">SIMA-PTBA</h1>
            <p class="text-gray-500 text-sm mt-2 leading-relaxed">
                {{ __('Lupa kata sandi? Beritahu kami alamat email Anda dan kami akan mengirimkan tautan reset kata sandi.') }}
            </p>
        </div>

        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
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
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 text-gray-700 placeholder-gray-400 shadow-sm"
                    placeholder="nama@contoh.com"
                >
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="space-y-4">
                <button
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                >
                    {{ __('Email Password Reset Link') }}
                </button>

                <div class="text-center">
                    <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium hover:underline">
                        {{ __('Kembali ke halaman login') }}
                    </a>
                </div>
            </div>
        </form>

        <p class="text-center text-gray-500 text-xs sm:text-sm mt-8">
            © {{ date('Y') }} PT Bukit Asam Tbk. All rights reserved.
        </p>
    </div>
</x-guest-layout>