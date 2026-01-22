<x-guest-layout>
    <x-slot name="title">
        Reset Password
    </x-slot>

    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        
        <div class="text-center mb-6">
            <img src="{{ asset('images/ptba.png') }}" alt="PTBA Logo" class="w-40 sm:w-48 mx-auto mb-4">
            
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">SIMA-PTBA</h1>
            <p class="text-gray-500 text-sm mt-2 leading-relaxed">
                Silakan buat kata sandi baru untuk akun Anda.
            </p>
        </div>

        <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
            @csrf

            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                    Email
                </label>
                <input 
                    id="email" 
                    type="email" 
                    name="email" 
                    value="{{ old('email', $request->email) }}" 
                    required 
                    readonly
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed focus:outline-none shadow-sm"
                >
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                    Kata Sandi Baru
                </label>
                <input 
                    id="password" 
                    type="password" 
                    name="password" 
                    required 
                    autocomplete="new-password"
                    autofocus
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 text-gray-700 placeholder-gray-400 shadow-sm"
                    placeholder="••••••••"
                >
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                    Konfirmasi Kata Sandi
                </label>
                <input 
                    id="password_confirmation" 
                    type="password" 
                    name="password_confirmation" 
                    required 
                    autocomplete="new-password"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-500 focus:outline-none transition duration-200 text-gray-700 placeholder-gray-400 shadow-sm"
                    placeholder="••••••••"
                >
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <button 
                type="submit" 
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 mt-2"
            >
                Reset Kata Sandi
            </button>
        </form>

        <p class="text-center text-gray-500 text-xs sm:text-sm mt-8">
            © {{ date('Y') }} PT Bukit Asam Tbk. All rights reserved.
        </p>
    </div>
</x-guest-layout>