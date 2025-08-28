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

            // Generate a random password
            $randomPassword = bin2hex(random_bytes(8));

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

            // Create default users for other roles
            $this->createOtherRoles();

            return 0;
        } catch (\Exception $e) {
            $this->error('Error creating super admin: ' . $e->getMessage());
            return 1;
        }
    }

    /**
     * Create other roles users.
     */
    protected function createOtherRoles()
    {
        $roles = ['admin', 'doctor', 'nurse', 'receptionist', 'pharmacist', 'lab_technician'];
        $dobOption = $this->option('dob');
        foreach ($roles as $role) {
            try {
                // Check if user with same username or email already exists
                $existingUser = User::where('username', $role)
                                    ->orWhere('email', $role . '@hospital.com')
                                    ->first();
                if ($existingUser) {
                    $this->error(ucfirst($role) . " already exists!");
                    continue;
                }
                // Use provided date of birth or generate a random one
                if ($dobOption) {
                    $dateOfBirth = $dobOption;
                } else {
                    $randomYear = rand(1970, 2000);
                    $randomMonth = str_pad(rand(1, 12), 2, '0', STR_PAD_LEFT);
                    $randomDay = str_pad(rand(1, 28), 2, '0', STR_PAD_LEFT);
                    $dateOfBirth = "$randomYear-$randomMonth-$randomDay";
                }

                $user = User::create([
                    'first_name' => ucfirst($role),
                    'last_name' => 'User',
                    'username' => $role,
                    'email' => $role . '@hospital.com',
                    'password' => Hash::make('password123'),
                    'role' => $role,
                    'is_admin' => $role === 'admin', // Only 'admin' role gets admin privileges
                    'is_super' => false, // Only super admin gets super privileges
                    'is_active' => true,
                    'is_verified' => true,
                    'gender' => 'male',
                    'date_of_birth' => $dateOfBirth,
                    'email_verified_at' => now()
                ]);
                $this->info(ucfirst($role) . " created successfully!");
                $this->info("Email: " . $user->email);
                $this->info("Username: " . $user->username);
                $this->info("Password: password123");
                $this->info("Role: " . $user->role);
            } catch (\Exception $e) {
                $this->error("Error creating $role: " . $e->getMessage());
            }
        }
    }
}
