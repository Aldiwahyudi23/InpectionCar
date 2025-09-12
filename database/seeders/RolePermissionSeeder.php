<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cache peran dan izin
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Daftar semua izin dari aplikasi utama
        $appPermissions = [
            'access dashboard',
            'view inspections',
            'start inspections',
            'create inspections',
            'cancel inspections',
            'store inspection results',
            'update vehicle details',
            'update conclusion',
            'upload images',
            'delete images',
            'final submit inspections',
            'review inspections',
            'download pdf reports',
            'send email reports',
            'view cars',
            'create cars',
            'manage brands',
            'manage models',
            'manage types',
            'manage car details',
            'view bantuan',
            'view coordinator dashboard',
            'assign inspections',
            'update inspection status',
        ];

        // Daftar semua model Filament yang membutuhkan izin CRUD
        $filamentModels = [
            'user',
            'brand',
            'model',
            'type',
            'detailmobil',
            'user session',
            'permission',
            'role',
            'region',
            'region team',
            'data inspeksi',
            'komponent',
            'inspection point',
            'kategori',
            'app menu',
            'menu point',
        ];

        // Daftar semua izin CRUD
        $crudActions = ['view', 'create', 'update', 'delete'];

        // Siapkan array untuk izin Filament
        $filamentPermissions = [];
        foreach ($filamentModels as $model) {
            foreach ($crudActions as $action) {
                $filamentPermissions[] = 'filament ' . $action . ' ' . $model;
            }
        }

        // Gabungkan semua izin menjadi satu array
        $allPermissions = array_merge($appPermissions, $filamentPermissions);

        // Buat semua izin yang terdaftar di database
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // --- Buat Peran dan Berikan Izin ---

        // 1. Peran Admin
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions($allPermissions);

        // 2. Peran Koordinator
        $coordinatorRole = Role::firstOrCreate(['name' => 'coordinator']);
        $coordinatorPermissions = [
                       'access dashboard',
            'view inspections',
            'start inspections',
            'create inspections',
            'cancel inspections',
            'store inspection results',
            'update vehicle details',
            'update conclusion',
            'upload images',
            'delete images',
            'final submit inspections',
            'review inspections',
            'download pdf reports',
            'send email reports',
            'view cars',
            'create cars',
            'manage brands',
            'manage models',
            'manage types',
            'manage car details',
            'view bantuan',
        ];
        $coordinatorRole->syncPermissions($coordinatorPermissions);

        // 3. Peran Inspektor
        $inspectorRole = Role::firstOrCreate(['name' => 'inspector']);
        $inspectorPermissions = [
            'access dashboard',
            'view inspections',
            'start inspections',
            'create inspections',
            'cancel inspections',
            'store inspection results',
            'update vehicle details',
            'update conclusion',
            'upload images',
            'delete images',
            'final submit inspections',
            'review inspections',
            'download pdf reports',
            'send email reports',
            'view cars',
            'create cars',
            'manage brands',
            'manage models',
            'manage types',
            'manage car details',
            'view bantuan',
        ];
        $inspectorRole->syncPermissions($inspectorPermissions);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}