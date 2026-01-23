<aside id="sidebar" 
       class="fixed top-0 left-0 z-50 w-72 h-screen bg-white border-r border-gray-200"
       x-cloak
       :class="{
           '-translate-x-full': !mobileSidebarOpen, 
           'translate-x-0': mobileSidebarOpen,
           'lg:translate-x-0': desktopSidebarOpen,
           'lg:-translate-x-full': !desktopSidebarOpen
       }">
       
    <div class="h-full px-4 py-6 overflow-y-auto" 
         x-data="{ scrollPos: 0 }"
         x-init="
             scrollPos = parseFloat(sessionStorage.getItem('sidebarScrollPos')) || 0;
             $nextTick(() => { $el.scrollTop = scrollPos; });
         "
         @scroll.passive="scrollPos = $el.scrollTop; sessionStorage.setItem('sidebarScrollPos', scrollPos)"> 
        
        {{-- HEADER SIDEBAR --}}
        <div class="flex items-center justify-between mb-8 px-2">
            {{-- Arahkan logo ke dashboard employee --}}
            <a href="{{ route('employee.dashboard') }}" wire:navigate class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center">
                    {{-- Pastikan path image sesuai --}}
                    <img src="{{ asset('images/headptba.png') }}" alt="Logo" class="h-6 w-6">
                </div>
                <div>
                    <h1 class="text-gray-900 font-bold text-lg leading-tight">Manajer Aset</h1>
                    <p class="text-gray-500 text-xs">Panel Karyawan</p>
                </div>
            </a>
            
            {{-- Tombol Close Mobile --}}
            <button @click="mobileSidebarOpen = false" class="lg:hidden text-gray-500 hover:bg-gray-100 p-2 rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <nav class="space-y-2">
            {{-- DASHBOARD (AKTIF) --}}
            <a href="{{ route('employee.dashboard') }}" wire:navigate
               @click="sessionStorage.setItem('sidebarScrollPos', $el.closest('.overflow-y-auto').scrollTop)"
               class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 group"
               :class="window.location.href.includes('dashboard') ? 'bg-gray-100 text-indigo-600 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'">
                <svg class="w-5 h-5 group-hover:text-gray-500" 
                     :class="window.location.href.includes('dashboard') ? 'text-indigo-600' : 'text-gray-400'"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                <span class="font-medium">Dashboard</span>
            </a>

            {{-- ASET --}}
            <a href="{{ route('employee.assets.index') }}" wire:navigate
               @click="sessionStorage.setItem('sidebarScrollPos', $el.closest('.overflow-y-auto').scrollTop)"
               class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 group"
               :class="window.location.href.includes('assets') ? 'bg-gray-100 text-indigo-600 font-semibold' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50'">
               
                <svg class="w-5 h-5 group-hover:text-gray-500" 
                     :class="window.location.href.includes('assets') ? 'text-indigo-600' : 'text-gray-400'"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="font-medium">Aset</span>
            </a>

            {{-- PINDAI QR (Route #) --}}
            <a href="#" 
               @click="sessionStorage.setItem('sidebarScrollPos', $el.closest('.overflow-y-auto').scrollTop)"
               class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 group text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500" 
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                </svg>
                <span class="font-medium">Pindai QR</span>
            </a>

            {{-- PEMELIHARAAN (Route #) --}}
             <a href="#"
               @click="sessionStorage.setItem('sidebarScrollPos', $el.closest('.overflow-y-auto').scrollTop)"
               class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 group text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span class="font-medium">Pemeliharaan</span>
            </a>

            {{-- LAPORAN (Route #) --}}
            <a href="#"
               @click="sessionStorage.setItem('sidebarScrollPos', $el.closest('.overflow-y-auto').scrollTop)"
               class="flex items-center space-x-3 px-4 py-3 rounded-xl transition-all duration-200 group text-gray-600 hover:text-gray-900 hover:bg-gray-50">
                <svg class="w-5 h-5 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="font-medium">Laporan</span>
            </a>
        </nav>
    </div>
</aside>