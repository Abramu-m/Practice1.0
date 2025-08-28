<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RadiologistSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a test radiologist user
        User::updateOrCreate(
            ['email' => 'radiologist@example.com'],
            [
                'name' => 'Dr. John Radiologist',
                'email' => 'radiologist@example.com',
                'password' => Hash::make('password'),
                'role' => 'radiologist',
                'is_admin' => false,
                'is_super' => false,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('Radiologist user created successfully!');
        $this->command->info('Email: radiologist@example.com');
        $this->command->info('Password: password');
    }
}
