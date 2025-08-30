<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
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



        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: aldiwahyudi1223@gmail.com');
        $this->command->info('Password: @Komando1223');
    }
}