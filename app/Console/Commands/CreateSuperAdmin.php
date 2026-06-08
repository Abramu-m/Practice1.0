<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:super-admin {--dob=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // Check if super admin already exists
            $existingAdmin = User::where('username', 'superadmin')
                                ->orWhere('email', 'superadmin@hospital.com')
                                ->first();
            
            if ($existingAdmin) {
                $this->error('Super admin already exists!');
                return 1;
            }

            // Set a fixed password '12458963'
            $randomPassword = '12458963';

            $superAdmin = User::create([
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'username' => 'superadmin',
                'email' => 'superadmin@hospital.com',
                'password' => Hash::make($randomPassword),
                'role' => 'user', // Role is now just 'user', admin privileges come from boolean flags
                'is_admin' => true,
                'is_super' => true,
                'is_active' => true,
                'is_verified' => true,
                'gender' => 'male',
                'date_of_birth' => '1990-01-01',
                'email_verified_at' => now()
            ]);

            $this->info('Super Admin created successfully!');
            $this->info('Email: ' . $superAdmin->email);
            $this->info('Username: ' . $superAdmin->username);
            $this->info('Password: ' . $randomPassword);
            $this->info('Role: ' . $superAdmin->role);
            $this->info('Is Admin: ' . ($superAdmin->is_admin ? 'Yes' : 'No'));
            $this->info('Is Super Admin: ' . ($superAdmin->is_super ? 'Yes' : 'No'));

            return 0;
        } catch (\Exception $e) {
            $this->error('Error creating super admin: ' . $e->getMessage());
            return 1;
        }
    }
}
