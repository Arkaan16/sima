<!DOCTYPE html>
{{-- 
    Layout Utama: Employee Layout
    Digunakan sebagai kerangka dasar untuk semua halaman dashboard karyawan.
    Menangani struktur HTML, aset (CSS/JS), dan state global untuk sidebar.
--}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Menetapkan judul halaman secara dinamis. Jika variabel $title tidak ada, gunakan default 'SIMA-PTBA'. --}}
    <title>{{ $title ?? 'SIMA-PTBA' }}</title>
    <link rel="icon" href="{{ asset('images/headptba.png') }}" type="image/png">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        
        /* SIDEBAR TRANSITIONS */
        /* Animasi hanya pada interaksi user, bukan saat load */
        #sidebar.sidebar-ready {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>

{{-- 
    Inisialisasi Alpine.js (x-data):
    1. mobileSidebarOpen: Mengontrol visibilitas sidebar pada layar kecil (mobile).
    2. desktopSidebarOpen: Mengambil preferensi user dari LocalStorage (tetap terbuka/tertutup saat reload).
    3. toggleDesktop(): Fungsi untuk mengubah status sidebar desktop dan menyimpan preferensi tersebut.
    4. x-init: Menambahkan class 'sidebar-ready' setelah DOM siap untuk mengaktifkan animasi transisi.
--}}
<body class="font-sans antialiased bg-gray-50"
      x-data="{ 
          mobileSidebarOpen: false,
          desktopSidebarOpen: localStorage.getItem('desktopSidebarOpen') === 'true' || localStorage.getItem('desktopSidebarOpen') === null,
          toggleDesktop() {
              this.desktopSidebarOpen = !this.desktopSidebarOpen;
              localStorage.setItem('desktopSidebarOpen', this.desktopSidebarOpen);
          }
      }"
      x-init="$nextTick(() => { document.getElementById('sidebar').classList.add('sidebar-ready'); })"
      :class="{ 'overflow-hidden': mobileSidebarOpen }">

    {{-- Mobile Overlay / Backdrop --}}
    {{-- 
        Overlay gelap yang muncul saat sidebar mobile terbuka.
        Klik pada overlay akan menutup sidebar (mobileSidebarOpen = false).
    --}}
    <div x-show="mobileSidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileSidebarOpen = false"
         class="fixed inset-0 z-40 bg-black/10 lg:hidden"
         x-cloak>
    </div>
    
    {{-- Panggil Component Sidebar --}}
    <x-employee-sidebar />
    
    {{-- Main Content --}}
    {{-- 
        Wrapper konten utama.
        Logic Class: Mengatur margin kiri (ml) secara reaktif. 
        Jika desktopSidebarOpen true, beri margin 72 (lebar sidebar), jika false margin 0.
    --}}
    <div id="mainContent" 
         class="min-h-screen"
         :class="desktopSidebarOpen ? 'lg:ml-72' : 'lg:ml-0'">
         
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
            <div class="px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    
                    {{-- Left Side --}}
                    <div class="flex items-center gap-3">
                        {{-- Tombol Mobile --}}
                        {{-- Hanya tampil di layar kecil (lg:hidden) untuk membuka sidebar mobile --}}
                        <button @click="mobileSidebarOpen = true" 
                                class="lg:hidden text-gray-600 hover:text-gray-900 p-2 rounded-lg hover:bg-gray-100 transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>

                        {{-- Tombol Toggle Desktop --}}
                        {{-- Hanya tampil di layar besar (hidden lg:flex). Mengubah icon panah/hamburger berdasarkan state --}}
                        <button @click="toggleDesktop()" 
                                class="hidden lg:flex text-gray-600 hover:text-gray-900 p-2 rounded-lg hover:bg-gray-100 transition items-center justify-center">
                            <svg x-show="desktopSidebarOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                            </svg>
                            <svg x-show="!desktopSidebarOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>

                    {{-- Right Side - Profile --}}
                    {{-- Dropdown Profile dengan state lokal 'open'. Menutup otomatis jika klik di luar area (@click.away). --}}
                    <div class="relative" x-data="{ open: false }" @click.away="open = false">
                        <button @click="open = !open" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-50 transition">
                            <div class="text-right hidden md:block">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">Karyawan</p>
                            </div>
                            {{-- Menampilkan inisial nama user sebagai avatar --}}
                            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold shadow-md">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            {{-- Rotasi icon panah dropdown saat aktif --}}
                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        {{-- Isi Dropdown Profile --}}
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                             
                            {{-- Info User (Hanya tampil di mobile karena di header desktop sudah ada) --}}
                            <div class="px-4 py-3 border-b border-gray-200 md:hidden">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ Auth::user()->email }}</p>
                            </div>

                            <a href="{{ route('profile.edit') }}" wire:navigate class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>Profil Saya</span>
                            </a>
        
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition">
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

        {{-- Area Konten Utama --}}
        {{-- Tempat dimana konten dari view lain (child) akan dirender --}}
        <main class="min-h-screen p-6">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>