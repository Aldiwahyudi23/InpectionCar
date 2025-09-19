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
                    'roles' => $user->getRoleNames(),
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





//================== untuk frond end agar tidak muncul sesuai dengan permission
// <template>
//   <div>
//     <ul>
//       <!-- Menu dashboard (semua user bisa lihat) -->
//       <li>
//         <Link href="/dashboard">Dashboard</Link>
//       </li>

//       <!-- Menu khusus dengan permission -->
//       <li v-if="can('view inspections')">
//         <Link href="/inspections">Inspections</Link>
//       </li>

//       <li v-if="can('manage cars')">
//         <Link href="/cars">Car Management</Link>
//       </li>
//     </ul>
//   </div>
// </template>

// <script setup>
// import { usePage } from '@inertiajs/vue3';

// const page = usePage();

// // Helper function: cek permission
// function can(permission) {
//   return page.props.auth.user?.permissions.includes(permission);
// }
// </script>

// ==============Jika untuk permission dan role di satuin tinggal panggil ini ===============

// <script setup>
// import { Link } from '@inertiajs/vue3';
// import { useAuth } from '@/composables/useAuth';

// const { hasRole, can, canRolePermission } = useAuth();
// </script>

// <template>
//   <nav>
//     <Link href="/dashboard">Dashboard</Link>

//     <!-- Hanya Admin / Coordinator yang punya permission view inspections -->
//     <Link
//       v-if="canRolePermission(['Admin','coordinator'], 'view inspections')"
//       href="/inspections"
//     >
//       Inspections
//     </Link>
//   </nav>
// </template>

// untuk mengambil role 

// <script setup>
// import { computed } from 'vue';
// import { usePage } from '@inertiajs/vue3';

// const page = usePage();

// // Ambil roles user
// const roles = page.props.global?.roles || [];

// // Cek apakah user punya role tertentu
// const isAdmin = computed(() => roles.includes('admin'));
// const isCoordinator = computed(() => roles.includes('coordinator'));

// // Ambil role tertentu saja (contoh: admin)
// const adminRole = computed(() => roles.find(role => role === 'admin') || null);

// // Ambil role tertentu saja (contoh: coordinator)
// const coordinatorRole = computed(() => roles.find(role => role === 'coordinator') || null);



// const page = usePage();

// // Ambil roles user dari global
// const roles = page.props.global?.roles || [];

// // Cek apakah user adalah admin atau coordinator
// const isAdminOrCoordinator = computed(() => {
//     return roles.includes('admin') || roles.includes('coordinator');
// });
// </script>

// <template>
//   <div>
//     <p>Admin Role: {{ adminRole }}</p>
//     <p>Coordinator Role: {{ coordinatorRole }}</p>

//     <!-- Contoh conditional rendering -->
//     <div v-if="isAdmin">Menu khusus admin</div>
//     <div v-if="isCoordinator">Menu khusus coordinator</div>
//   </div>
// </template>
