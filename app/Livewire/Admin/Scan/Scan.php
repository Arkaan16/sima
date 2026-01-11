<?php

namespace App\Livewire\Admin\Scan;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

/**
 * Komponen Livewire untuk Pemindai QR Code Aset
 * 
 * Komponen ini menampilkan interface pemindai QR code menggunakan 
 * library html5-qrcode untuk memindai kode QR aset secara real-time
 * menggunakan kamera perangkat.
 * 
 * Fitur:
 * - Auto-start kamera saat halaman dimuat
 * - Deteksi QR code secara real-time
 * - Redirect otomatis ke halaman detail aset setelah scan berhasil
 * - Error handling untuk masalah izin kamera
 * - Responsive design untuk mobile dan desktop
 * 
 * @package App\Livewire\Admin\Scan
 */
#[Layout('components.layouts.admin')]
#[Title('Pemindai QR Code Aset')]
class Scan extends Component
{
    /**
     * Render view komponen scanner
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.admin.scan.scan');
    }
}