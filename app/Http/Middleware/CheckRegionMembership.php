<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRegionMembership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
         $user = Auth::user();

        // kalau user belum login
        if (!$user) {
            return redirect()->route('login');
        }

        // cek apakah user ada di region team aktif
        $regionTeam = $user->regionTeams()
            ->where('status', 'active')
            ->first();

        if (!$regionTeam) {
            // kalau user buka selain halaman inactive, arahkan ke inactive
            if (!$request->routeIs('region.inactive')) {
                return redirect()->route('region.inactive');
            }
        } 

        return $next($request);
    }
}
