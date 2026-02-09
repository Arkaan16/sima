<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    /**
     * Siapa yang boleh melihat daftar aset? (Semua User Login)
     */
    public function viewAny(User $user): bool
    {
        return true; 
    }

    /**
     * Siapa yang boleh melihat detail aset? (Semua User Login)
     */
    public function view(User $user, Asset $asset): bool
    {
        return true;
    }

    /**
     * Siapa yang boleh membuat aset? (Hanya Admin)
     */
    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Siapa yang boleh mengedit aset? (Hanya Admin)
     */
    public function update(User $user, Asset $asset): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Siapa yang boleh menghapus aset? (Hanya Admin)
     */
    public function delete(User $user, Asset $asset): bool
    {
        return $user->role === 'admin';
    }
    
    /**
     * Siapa yang boleh download QR? (Hanya Admin - Custom Logic)
     */
    public function downloadQr(User $user): bool
    {
        // Izinkan jika role adalah admin ATAU employee
        return in_array($user->role, ['admin', 'employee']);
    }
}