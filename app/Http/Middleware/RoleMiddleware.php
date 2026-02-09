<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        // Cek apakah user sudah login
        if (!$request->user()) {
            abort(403, 'Unauthorized. Silakan login terlebih dahulu.');
        }

        // Pisahkan role-role yang diizinkan berdasarkan separator |
        $allowedRoles = explode('|', $roles);

        // Cek apakah role user ada dalam daftar role yang diizinkan
        if (!in_array($request->user()->role, $allowedRoles)) {
            abort(403, 'Unauthorized. Role Anda tidak diizinkan mengakses halaman ini.');
        }

        return $next($request);
    }
}
