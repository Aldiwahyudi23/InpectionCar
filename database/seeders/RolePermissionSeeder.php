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
             // Dashboard & Welcome
            'FrontEnd.access dashboard',
            
            // Inspections - View
            'FrontEnd.view inspections',
            'FrontEnd.history inspections',
            'FrontEnd.log inspections',
            'FrontEnd.cancel inspections',
            
            // Inspections - Create
            'FrontEnd.create inspections',
            
            // Inspections - Start & Process
            'FrontEnd.start inspections',
            'FrontEnd.final submit inspections',
            
            // Inspections - Review
            'FrontEnd.review inspections',
            'FrontEnd.revisi inspections',
            'FrontEnd.review inspection report',
            'FrontEnd.approve inspections report',
            'FrontEnd.download pdf',
            
            // Reports & Communications
            'FrontEnd.send email reports',
            'FrontEnd.send whatsapp reports',
            
            // Cars Management
            'FrontEnd.view cars',
            'FrontEnd.create cars',
            'FrontEnd.manage brands',
            'FrontEnd.manage models',
            'FrontEnd.manage types',
            'FrontEnd.manage car details',
            
            // Bantuan
            'FrontEnd.view bantuan',
            
            // Coordinator Functions
            'FrontEnd.view coordinator dashboard',
            'FrontEnd.coordinator assign inspections',
            'FrontEnd.coordinator update inspection status',
            
            // Team Management
            'FrontEnd.view teams',
            'FrontEnd.add Team',
            'FrontEnd.settings Team',
            
            // Transaction & Finance
            'FrontEnd.update transaction',
            'FrontEnd.view finance',
            'FrontEnd.finance report',
            'FrontEnd.finance setor'
        ];

        // Buat semua izin yang terdaftar di database
        foreach ($appPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // --- Buat Peran dan Berikan Izin ---

        // 1. Peran Admin
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $adminRole->syncPermissions($appPermissions);
        // $adminRole->givePermissionTo(Permission::all());

        // 2. Peran Koordinator
        $coordinatorRole = Role::firstOrCreate(['name' => 'coordinator']);
        $coordinatorPermissions = [
 // Dashboard & Welcome
            'FrontEnd.access dashboard',
            
            // Inspections - View
            'FrontEnd.view inspections',
            'FrontEnd.history inspections',
            'FrontEnd.log inspections',
            'FrontEnd.cancel inspections',
            
            // Inspections - Create
            'FrontEnd.create inspections',
            
            // Inspections - Start & Process
            'FrontEnd.start inspections',
            'FrontEnd.final submit inspections',
            
            // Inspections - Review
            'FrontEnd.review inspections',
            'FrontEnd.revisi inspections',
            'FrontEnd.review inspection report',
            'FrontEnd.approve inspections report',
            'FrontEnd.download pdf',
            
            // Reports & Communications
            'FrontEnd.send email reports',
            'FrontEnd.send whatsapp reports',
            
            // Cars Management
            'FrontEnd.view cars',
            'FrontEnd.create cars',
            'FrontEnd.manage brands',
            'FrontEnd.manage models',
            'FrontEnd.manage types',
            'FrontEnd.manage car details',
            
            // Bantuan
            'FrontEnd.view bantuan',
            
            // Coordinator Functions
            'FrontEnd.view coordinator dashboard',
            'FrontEnd.coordinator assign inspections',
            'FrontEnd.coordinator update inspection status',
            
            // Team Management
            'FrontEnd.view teams',
            'FrontEnd.add Team',
            'FrontEnd.settings Team',
            
            // Transaction & Finance
            'FrontEnd.update transaction',
            'FrontEnd.view finance',
            'FrontEnd.finance report',
            'FrontEnd.finance setor'
        ];
        $coordinatorRole->syncPermissions($coordinatorPermissions);

        // 3. Peran Inspektor
        $inspectorRole = Role::firstOrCreate(['name' => 'inspector']);
        $inspectorPermissions = [
 // Dashboard & Welcome
            'FrontEnd.access dashboard',
            
            // Inspections - View
            'FrontEnd.view inspections',
            'FrontEnd.history inspections',
            'FrontEnd.log inspections',
            'FrontEnd.cancel inspections',
            
            // Inspections - Create
            'FrontEnd.create inspections',
            
            // Inspections - Start & Process
            'FrontEnd.start inspections',
            'FrontEnd.final submit inspections',
            
            // Inspections - Review
            'FrontEnd.review inspections',
            'FrontEnd.revisi inspections',
            'FrontEnd.review inspection report',
            'FrontEnd.approve inspections report',
            'FrontEnd.download pdf',
            
            // Reports & Communications
            'FrontEnd.send email reports',
            'FrontEnd.send whatsapp reports',
            
            // Cars Management
            'FrontEnd.view cars',
            'FrontEnd.create cars',
            'FrontEnd.manage brands',
            'FrontEnd.manage models',
            'FrontEnd.manage types',
            'FrontEnd.manage car details',
            
            // Bantuan
            'FrontEnd.view bantuan',
            
            // // Coordinator Functions
            // 'FrontEnd.view coordinator dashboard',
            // 'FrontEnd.coordinator assign inspections',
            // 'FrontEnd.coordinator update inspection status',
            
            // Team Management
            'FrontEnd.view teams',
            'FrontEnd.add Team',
            'FrontEnd.settings Team',
            
            // Transaction & Finance
            'FrontEnd.update transaction',
            'FrontEnd.view finance',
            'FrontEnd.finance report',
            'FrontEnd.finance setor'
        ];
        $inspectorRole->syncPermissions($inspectorPermissions);

        $this->command->info('Roles and permissions seeded successfully!');
    }
}
