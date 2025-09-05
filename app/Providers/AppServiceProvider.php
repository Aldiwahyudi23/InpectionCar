<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Inertia::share([
            'global' => function () {
                $user = Auth::user();

                if (! $user) {
                    return null;
                }

                $regionTeam = $user->regionTeams()
                    ->where('status', 'active')
                    ->with('region')
                    ->first();

                return [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'region' => $regionTeam?->region,
                    'has_roles' => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ];
            },
        ]);
    }
}

// <script setup>
// import { usePage } from '@inertiajs/vue3'

// const page = usePage()
// const user = page.props.auth.user
// const region = page.props.auth.region
// const role = page.props.auth.region_role
// </script>

// <template>
//   <div>
//     <h1>Halo, {{ user.name }}</h1>
//     <p v-if="region">Region: {{ region.name }} (Role: {{ role }})</p>
//     <p v-else>Akun anda belum bisa digunakan.</p>
//   </div>
// </template>
