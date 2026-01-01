<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Panel')</title>
    <link rel="icon" href="{{ asset('images/headptba.png') }}" type="image/png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        /* Perbaikan Transisi Sidebar */
        .sidebar-transition { 
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* Perbaikan Overlay agar Smooth dan Tidak Terlalu Gelap */
        #mobileMenuOverlay {
            transition: opacity 0.3s ease-in-out, visibility 0.3s;
            background-color: rgba(0, 0, 0, 0.4); /* Mengganti bg-black bg-opacity-50 agar lebih soft */
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
    </style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div id="mobileMenuOverlay" class="fixed inset-0 z-40 lg:hidden overlay-hidden"></div>
    
    <x-admin-sidebar />
    
    <div class="lg:ml-72">
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <button id="openSidebarMobile" class="lg:hidden text-gray-600 hover:text-gray-900 p-2 rounded-lg hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                    <div class="flex-1 flex items-center space-x-3 ml-4 lg:ml-0">
                        <h2 class="text-xl font-semibold text-gray-800">Dashboard Admin</h2>
                    </div>
                </div>
            </div>
        </nav>

        <main class="min-h-screen">
            @yield('content')
        </main>
    </div>
    
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileMenuOverlay');
        const openBtn = document.getElementById('openSidebarMobile');
        const closeBtn = document.getElementById('closeSidebarMobile');
        
        // Membuka Sidebar
        openBtn.addEventListener('click', () => {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('overlay-hidden');
            overlay.classList.add('overlay-visible');
        });
        
        // Menutup Sidebar
        const closeSidebar = () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.remove('overlay-visible');
            overlay.classList.add('overlay-hidden');
        };

        closeBtn.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);

        // Dropdown Toggle (Logika Asli Anda)
        const dropdownToggle = document.getElementById('dropdownToggle');
        const dropdownMenu = document.getElementById('dropdownMenu');
        const dropdownIcon = document.getElementById('dropdownIcon');
        
        if(dropdownToggle) {
            dropdownToggle.addEventListener('click', () => {
                const isOpen = dropdownMenu.classList.contains('max-h-0');
                dropdownMenu.classList.toggle('max-h-0', !isOpen);
                dropdownMenu.classList.toggle('max-h-96', isOpen);
                dropdownMenu.classList.toggle('opacity-0', !isOpen);
                dropdownMenu.classList.toggle('opacity-100', isOpen);
                dropdownIcon.classList.toggle('rotate-180', isOpen);
            });
        }
    </script>

    @livewireScripts
    
</body>
</html>