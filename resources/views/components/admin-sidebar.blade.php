<aside id="sidebar" class="fixed top-0 left-0 z-50 w-72 h-screen bg-white border-r border-gray-200 sidebar-transition -translate-x-full lg:translate-x-0">
    <div class="h-full px-4 py-6 overflow-y-auto scrollbar-hide"> 
        <div class="flex items-center justify-between mb-8 px-2">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center">
                    <img src="{{ asset('images/headptba.png') }}" alt="Logo" class="h-6 w-6">
                </div>
                <div>
                    <h1 class="text-gray-900 font-bold text-lg leading-tight">Manajer Aset</h1>
                    <p class="text-gray-500 text-xs">Panel Admin</p>
                </div>
            </a>
            <button id="closeSidebarMobile" class="lg:hidden text-gray-500 hover:bg-gray-100 p-2 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <nav class="space-y-2">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 group
               {{ request()->routeIs('admin.dashboard') ? 'bg-gray-100 text-indigo-600 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('admin.dashboard') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-500' }}" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="font-medium">Dashboard</span>
            </a>

            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 group text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span class="font-medium">Pengguna</span>
            </a>

            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 group text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="font-medium">Aset</span>
            </a>

            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 group text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span class="font-medium">Pindai QR</span>
            </a>

            <div class="space-y-1">
                <button id="dropdownToggle" class="w-full flex items-center justify-between space-x-3 px-4 py-3 rounded-xl transition-all duration-200 group text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4" />
                        </svg>
                        <span class="font-medium">Data Master</span>
                    </div>
                    <svg id="dropdownIcon" class="w-4 h-4 transition-transform duration-200 {{ request()->routeIs('admin.locations.*') ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                
                <div id="dropdownMenu" class="pl-4 space-y-1 overflow-hidden transition-all duration-200 ease-in-out {{ request()->routeIs('admin.locations.*') ? 'max-h-96 opacity-100' : 'max-h-0 opacity-0' }}">
                    <a href="{{ route('admin.locations.index') }}"
                    class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm
                            {{ request()->routeIs('admin.locations.*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}">
                        <span>Lokasi</span>
                    </a>
                    <a href="#" class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"><span>Kategori</span></a>
                    <a href="#" class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"><span>Status</span></a>
                    <a href="#" class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"><span>Model Perangkat</span></a>
                    <a href="#" class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"><span>Pabrikan</span></a>
                    <a href="#" class="flex items-center space-x-3 px-4 py-2 rounded-lg text-sm text-gray-600 hover:bg-gray-50"><span>Klien</span></a>
                </div>
            </div>

            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 group text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="font-medium">Pemeliharaan</span>
            </a>
            <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 group text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="font-medium">Laporan</span>
            </a>
        </nav>

        <div class="my-6 border-t border-gray-200"></div>
        
        <div class="mt-auto">
            <div class="bg-gray-50 rounded-xl p-4 mb-4">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-lg flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <p class="text-gray-800 font-medium text-sm leading-tight">{{ Auth::user()->name }}</p>
                        <p class="text-gray-500 text-xs">Administrator</p>
                    </div>
                </div>
                
                <div class="space-y-2">
                    <a href="{{ route('profile.edit') }}" class="flex items-center space-x-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        <span>Profil</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center space-x-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-all duration-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</aside>