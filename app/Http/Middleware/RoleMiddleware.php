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
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Cek apakah user sudah login dan punya role yang sesuai
        if (!$request->user() || $request->user()->role !== $role) {
            abort(403, 'Unauthorized. Role Anda tidak diizinkan mengakses halaman ini.');
        }

        return $next($request);
    }
}
