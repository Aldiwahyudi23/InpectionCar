<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

class CheckSpatieRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,  string $role): Response
    {
          // Cek jika user tidak authenticated
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Cek role menggunakan Spatie permission
        if (!$request->user()->hasRole($role)) {        
            // Redirect ke halaman error 403
            return redirect()->route('error.403');
        }

        return $next($request);
    }
}
