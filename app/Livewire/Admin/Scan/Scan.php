<?php

namespace App\Livewire\Admin\Scan;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

/**
 * Class Scan
 *
 * Komponen Livewire yang berfungsi sebagai antarmuka utama fitur pemindai QR Code.
 * Bertanggung jawab untuk memuat halaman yang mengintegrasikan logika pemindaian fisik
 * (menggunakan kamera perangkat) dan meneruskan hasil pindaian ke sistem manajemen aset.
 *
 * @package App\Livewire\Admin\Scan
 */
#[Layout('components.layouts.admin')]
#[Title('Pemindai QR Code Aset')]
class Scan extends Component
{
    /**
     * Merender tampilan antarmuka pemindai.
     * View ini berisi inisialisasi library scanner berbasis JavaScript.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        // 1. Tampilkan view halaman scan
        return view('livewire.admin.scan.scan');
    }
}