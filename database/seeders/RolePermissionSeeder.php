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
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // User Management
            'view users',
            'create users',
            'edit users',
            'delete users',
            
            // Inspection Management
            'view inspections',
            'create inspections',
            'edit inspections',
            'delete inspections',
            'approve inspections',
            'reject inspections',
            
            // Report Management
            'view reports',
            'generate reports',
            'export reports',
            
            // Configuration
            'manage settings',
            'manage categories',
            'manage components',
            
            // Dashboard
            'view dashboard',
            'view admin dashboard',
            'view inspector dashboard',
            'view coordinator dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminPermissions = [
            'view users', 'create users', 'edit users', 'delete users',
            'view inspections', 'create inspections', 'edit inspections', 'delete inspections', 'approve inspections', 'reject inspections',
            'view reports', 'generate reports', 'export reports',
            'manage settings', 'manage categories', 'manage components',
            'view dashboard', 'view admin dashboard',
        ];
        $adminRole->syncPermissions($adminPermissions);

        $coordinatorRole = Role::firstOrCreate(['name' => 'coordinator']);
        $coordinatorPermissions = [
            'view inspections', 'create inspections', 'edit inspections',
            'view reports', 'generate reports',
            'view dashboard', 'view coordinator dashboard',
        ];
        $coordinatorRole->syncPermissions($coordinatorPermissions);

        $inspectorRole = Role::firstOrCreate(['name' => 'inspector']);
        $inspectorPermissions = [
            'view inspections', 'create inspections',
            'view dashboard', 'view inspector dashboard',
        ];
        $inspectorRole->syncPermissions($inspectorPermissions);

        $inspectorRole = Role::firstOrCreate(['name' => 'region_admin']);
        $inspectorPermissions = [
            'view inspections', 'create inspections',
            'view dashboard', 'view inspector dashboard',
        ];
        $inspectorRole->syncPermissions($inspectorPermissions);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}