<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek jika user sudah login dan status tidak aktif
        if (Auth::check() && !Auth::user()->is_active) {
            // Jika request adalah API, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Akun Anda tidak aktif. Silakan hubungi administrator.',
                    'redirect' => route('account.inactive')
                ], 403);
            }
            
            // Jika bukan API, redirect ke halaman inactive
            return redirect()->route('account.inactive');
        }

        return $next($request);
    }
}
