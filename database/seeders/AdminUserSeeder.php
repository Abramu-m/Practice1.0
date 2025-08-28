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

        // Create Admin
        User::updateOrCreate(
            ['email' => 'admin@hospital.com'],
            [
                'first_name' => 'Hospital',
                'last_name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@hospital.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
                'is_verified' => true,
                'verified_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        // Create some sample unverified users for testing
        User::updateOrCreate(
            ['email' => 'doctor@hospital.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Doe',
                'username' => 'johndoe',
                'email' => 'doctor@hospital.com',
                'password' => Hash::make('password123'),
                'role' => 'doctor',
                'is_active' => true,
                'is_verified' => false, // This will need admin verification
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'nurse@hospital.com'],
            [
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'username' => 'janesmith',
                'email' => 'nurse@hospital.com',
                'password' => Hash::make('password123'),
                'role' => 'nurse',
                'is_active' => true,
                'is_verified' => false, // This will need admin verification
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Admin users created successfully!');
        $this->command->info('Super Admin - Email: superadmin@hospital.com, Password: password123');
        $this->command->info('Admin - Email: admin@hospital.com, Password: password123');
        $this->command->info('Sample unverified users created for testing verification functionality.');
    }
}
