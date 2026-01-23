<?php

namespace App\Livewire\Employee\Scan;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;

/**
 * Class Scan (Employee Version)
 *
 * Komponen Livewire yang berfungsi sebagai antarmuka pemindai QR Code untuk karyawan.
 * Sama persis dengan versi Admin, namun hasil scan akan diarahkan ke halaman detail aset karyawan.
 *
 * @package App\Livewire\Employee\Scan
 */
#[Layout('components.layouts.employee')]
#[Title('Pemindai QR Code Aset')]
class Scan extends Component
{
    /**
     * Merender tampilan antarmuka pemindai.
     *
     * @return \Illuminate\View\View
     */
    public function render()
    {
        return view('livewire.employee.scan.scan');
    }
}