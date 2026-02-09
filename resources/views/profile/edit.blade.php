<x-layouts.app title="Profil Saya">
    {{-- Container Utama --}}
    <div class="bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Header Section --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Pengaturan Profil</h1>
                    <p class="mt-1 text-gray-500 text-sm">Kelola informasi pribadi dan keamanan akun Anda</p>
                </div>
            </div>

            {{-- Grid Layout --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">

                {{-- Card 1: Update Informasi Profil --}}
                <div class="bg-white shadow-xl shadow-blue-900/5 rounded-2xl border border-gray-100 overflow-hidden h-full flex flex-col hover:shadow-2xl hover:shadow-blue-900/10 transition-shadow duration-300">
                    <div class="bg-gradient-to-r from-blue-500 to-indigo-600 h-1.5"></div>
                    <div class="p-6 flex-1">
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="flex-shrink-0 w-10 h-10 bg-blue-50 rounded-lg flex items-center justify-center border border-blue-100">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Informasi Profil</h2>
                                <p class="text-xs text-gray-500">Perbarui identitas dan email.</p>
                            </div>
                        </div>
                        
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                {{-- Card 2: Update Password --}}
                <div class="bg-white shadow-xl shadow-green-900/5 rounded-2xl border border-gray-100 overflow-hidden h-full flex flex-col hover:shadow-2xl hover:shadow-green-900/10 transition-shadow duration-300">
                    <div class="bg-gradient-to-r from-emerald-500 to-teal-600 h-1.5"></div>
                    <div class="p-6 flex-1">
                        <div class="flex items-center space-x-4 mb-6">
                            <div class="flex-shrink-0 w-10 h-10 bg-emerald-50 rounded-lg flex items-center justify-center border border-emerald-100">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 class="text-lg font-bold text-gray-900">Keamanan Akun</h2>
                                <p class="text-xs text-gray-500">Perbarui kata sandi Anda.</p>
                            </div>
                        </div>

                        @include('profile.partials.update-password-form')
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-layouts.app>