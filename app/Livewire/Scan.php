<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;

#[Title('Scan QR Code Aset')]
class Scan extends Component
{
    public function render()
    {
        // Langsung render view. 
        // Layout otomatis menggunakan 'components.layouts.app' (Default Livewire)
        // karena logika tampilan sidebar Admin/Employee sudah di-handle di dalam file layout tersebut.
        return view('livewire.scan');
    }
}