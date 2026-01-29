<?php

namespace App\Policies;

use App\Models\Maintenance;
use App\Models\User;

class MaintenancePolicy
{
    // ADMIN: Boleh segalanya
    public function before(User $user, $ability)
    {
        if ($user->role === 'admin') {
            return true;
        }
    }

    // INDEX: Boleh semua (sama seperti Asset)
    public function viewAny(User $user): bool
    {
        return true; 
    }

    // SHOW: Boleh semua (sama seperti Asset)
    // ATAU: Batasi hanya pelapor & teknisi
    public function view(User $user, Maintenance $maintenance): bool
    {
        // OPSI 1: Bebas seperti Asset (Recommended agar employee bisa lihat progress)
        return true; 

        // OPSI 2: Hanya Pelapor (jika ada kolom reporter_id) ATAU Teknisi
        // return $maintenance->reporter_id === $user->id 
        //     || $maintenance->technicians()->where('users.id', $user->id)->exists();
    }

    // CREATE: Boleh semua
    public function create(User $user): bool
    {
        return true;
    }

    // EDIT: Perlu dilonggarkan
    public function update(User $user, Maintenance $maintenance): bool
    {
        // Izinkan jika user adalah teknisi yang bertugas
        $isTechnician = $maintenance->technicians()->where('users.id', $user->id)->exists();
        
        // Izinkan juga (opsional) jika user adalah pelapor/pembuat data (jika Anda simpan created_by/reporter_id)
        // $isReporter = $maintenance->reporter_id === $user->id;

        // Untuk sekarang, karena Employee biasanya juga bisa jadi teknisi, logic teknisi dipertahankan.
        // TAPI, jika ingin seperti Asset (hanya Admin yang edit), return false.
        // Jika ingin Employee bebas edit:
        
        return $isTechnician || $user->role === 'employee'; 
    }

    // DELETE: Tetap false untuk employee
    public function delete(User $user, Maintenance $maintenance): bool
    {
        return false;
    }
}