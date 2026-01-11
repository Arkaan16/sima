<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'SIMA-PTBA' }}</title>
    <link rel="icon" href="{{ asset('images/headptba.png') }}" type="image/png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        
        /* SIDEBAR TRANSITIONS */
        #sidebar {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }
        
        /* Sidebar Hidden State - KHUSUS UNTUK DESKTOP */
        #sidebar.sidebar-hidden-desktop {
            transform: translateX(-100%) !important;
        }
        
        /* Content Transitions */
        #mainContent {
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Scrollbar Hide */
        /* .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; } */

        /* Mobile Overlay */
        #mobileMenuOverlay {
            transition: opacity 0.3s ease-in-out, visibility 0.3s;
            background-color: rgba(0, 0, 0, 0.4);
        }
        .overlay-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }
        .overlay-visible {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        /* Profile Dropdown Animation */
        .profile-dropdown {
            transform-origin: top right;
            transition: all 0.2s ease-out;
        }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div id="mobileMenuOverlay" class="fixed inset-0 z-40 lg:hidden overlay-hidden"></div>
    
    <x-admin-sidebar />
    
    {{-- Main Content --}}
    <div id="mainContent" class="lg:ml-72">
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    {{-- Left Side --}}
                    <div class="flex items-center gap-3">
                        {{-- Tombol Mobile --}}
                        <button id="openSidebarMobile" class="lg:hidden text-gray-600 hover:text-gray-900 p-2 rounded-lg hover:bg-gray-100 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>

                        {{-- Tombol Toggle Desktop --}}
                        <button id="toggleSidebarDesktop" class="hidden lg:flex text-gray-600 hover:text-gray-900 p-2 rounded-lg hover:bg-gray-100 transition items-center justify-center">
                            <svg id="sidebarIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path id="iconHide" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                                <path id="iconShow" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"></path>
                            </svg>
                        </button>
                        
                        
                    </div>

                    {{-- Right Side - Profile Dropdown --}}
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button 
                            @click="open = !open"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 transition"
                        >
                            <div class="text-right hidden md:block">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">Administrator</p>
                            </div>
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <svg 
                                class="w-4 h-4 text-gray-400 transition-transform duration-200"
                                :class="{ 'rotate-180': open }"
                                fill="none" 
                                stroke="currentColor" 
                                viewBox="0 0 24 24"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div 
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 profile-dropdown"
                        >
                            {{-- User Info (Mobile) --}}
                            <div class="px-4 py-3 border-b border-gray-200 md:hidden">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ Auth::user()->email }}</p>
                            </div>

                            {{-- Menu Items --}}
                            <a 
                                href="{{ route('profile.edit') }}" 
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition"
                            >
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>Profil Saya</span>
                            </a>
        
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button 
                                    type="submit" 
                                    class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition"
                                >
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main class="min-h-screen p-6">
            {{ $slot }}
        </main>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const overlay = document.getElementById('mobileMenuOverlay');
            const openBtnMobile = document.getElementById('openSidebarMobile');
            const closeBtnMobile = document.getElementById('closeSidebarMobile');
            const toggleBtnDesktop = document.getElementById('toggleSidebarDesktop');
            const iconHide = document.getElementById('iconHide');
            const iconShow = document.getElementById('iconShow');

            if (!sidebar || !toggleBtnDesktop) {
                console.error('❌ Sidebar atau toggle button tidak ditemukan!');
                return;
            }

            let isSidebarHidden = false;

            // MOBILE: Open Sidebar
            if (openBtnMobile) {
                openBtnMobile.addEventListener('click', () => {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('overlay-hidden');
                    overlay.classList.add('overlay-visible');
                });
            }
            
            // MOBILE: Close Sidebar
            const closeSidebarMobile = () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.remove('overlay-visible');
                overlay.classList.add('overlay-hidden');
            };

            if (closeBtnMobile) {
                closeBtnMobile.addEventListener('click', closeSidebarMobile);
            }
            if (overlay) {
                overlay.addEventListener('click', closeSidebarMobile);
            }

            // DESKTOP: Toggle Sidebar
            toggleBtnDesktop.addEventListener('click', () => {
                isSidebarHidden = !isSidebarHidden;

                if (isSidebarHidden) {
                    sidebar.classList.add('sidebar-hidden-desktop');
                    mainContent.style.marginLeft = '0';
                    iconHide.classList.add('hidden');
                    iconShow.classList.remove('hidden');
                    localStorage.setItem('sidebarHidden', 'true');
                } else {
                    sidebar.classList.remove('sidebar-hidden-desktop');
                    mainContent.style.marginLeft = '';
                    iconHide.classList.remove('hidden');
                    iconShow.classList.add('hidden');
                    localStorage.setItem('sidebarHidden', 'false');
                }
            });

            // RESTORE STATE
            const savedState = localStorage.getItem('sidebarHidden');
            if (savedState === 'true') {
                sidebar.classList.add('sidebar-hidden-desktop');
                mainContent.style.marginLeft = '0';
                iconHide.classList.add('hidden');
                iconShow.classList.remove('hidden');
                isSidebarHidden = true;
            }

            // DROPDOWN TOGGLE
            const dropdownToggle = document.getElementById('dropdownToggle');
            const dropdownMenu = document.getElementById('dropdownMenu');
            const dropdownIcon = document.getElementById('dropdownIcon');
            
            if(dropdownToggle && dropdownMenu && dropdownIcon) {
                dropdownToggle.addEventListener('click', () => {
                    const isOpen = dropdownMenu.classList.contains('max-h-0');
                    dropdownMenu.classList.toggle('max-h-0', !isOpen);
                    dropdownMenu.classList.toggle('max-h-96', isOpen);
                    dropdownMenu.classList.toggle('opacity-0', !isOpen);
                    dropdownMenu.classList.toggle('opacity-100', isOpen);
                    dropdownIcon.classList.toggle('rotate-180', isOpen);
                });
            }
        });
    </script>

    @livewireScripts
    
</body>
</html>