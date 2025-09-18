<?php

namespace Database\Seeders;

use App\Models\Team\Region;
use App\Models\Team\RegionTeam;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'aldiwahyudi1223@gmail.com'],
            [
                'name' => 'Aldi Wahyudi',
                'password' => Hash::make('@Komando1223'),
                'email_verified_at' => now(),
            ]
        );

        // Assign admin role
        $admin->assignRole('Admin');

        // 2. Create Region (Bandung 1)
        $region = Region::firstOrCreate(
            ['code' => 'BDG1'], // pastikan unique
            [
                'name' => 'Bandung 1',
                'address' => '-',
                'city' => 'Bandung',
                'province' => 'Jawa Barat',
                'is_active' => true,
                'settings' => json_encode([
                    'income_owner' => 100,
                    'income_coordinator' => 0,
                ]),
            ]
        );

        // 3. Create RegionTeam (link admin user ke region Bandung 1)
        RegionTeam::firstOrCreate(
            [
                'region_id' => $region->id,
                'user_id' => $admin->id,
            ],
            [
                'status' => 'active',
                'description' => null,
                'settings' => json_encode([
                    'inspection_price_self' => 0,
                    'inspection_price_external' => 0,
                ]),
            ]
        );

        // Output info ke terminal
        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: aldiwahyudi1223@gmail.com');
        $this->command->info('Password: @Komando1223');
        $this->command->info('Region "Bandung 1" created successfully!');
        $this->command->info('RegionTeam linked successfully!');
    }
}
