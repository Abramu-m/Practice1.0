<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Super Admin
        User::updateOrCreate(
            ['email' => 'superadmin@hospital.com'],
            [
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@hospital.com',
                'password' => Hash::make('password123'),
                'role' => 'super_admin',
                'is_active' => true,
                'is_verified' => true,
                'verified_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin user created successfully!');
        $this->command->info('Super Admin - Email: superadmin@hospital.com, Password: password123');
    }
}
