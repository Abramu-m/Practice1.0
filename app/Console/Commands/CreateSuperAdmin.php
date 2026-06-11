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
    protected $signature = 'create:super-admin';

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
            // Prompt for the super admin's credentials and details
            $firstName = $this->ask('First name');
            $lastName  = $this->ask('Last name');
            $username  = $this->ask('Username');
            $email     = $this->ask('Email');

            // Ask for the password (hidden input) and confirm it
            do {
                $password = $this->secret('Password (at least 5 characters)');
                $confirm  = $this->secret('Confirm password');

                if (strlen($password) < 5) {
                    $this->error('Password must be at least 5 characters. Please try again.');
                    continue;
                }

                if ($password !== $confirm) {
                    $this->error('Passwords do not match. Please try again.');
                }
            } while (strlen($password) < 5 || $password !== $confirm);

            $gender = $this->choice('Gender', ['male', 'female'], 0);
            $dob    = $this->ask('Date of birth (YYYY-MM-DD)');

            // Check if a user with this username or email already exists
            $existingAdmin = User::where('username', $username)
                                ->orWhere('email', $email)
                                ->first();

            if ($existingAdmin) {
                $this->error('A user with this username or email already exists!');
                return 1;
            }

            $superAdmin = User::create([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $username,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'user', // Role is now just 'user', admin privileges come from boolean flags
                'is_admin' => true,
                'is_super' => true,
                'is_active' => true,
                'is_verified' => true,
                'gender' => $gender,
                'date_of_birth' => $dob,
                'email_verified_at' => now()
            ]);

            $this->info('Super Admin created successfully!');
            $this->info('Email: ' . $superAdmin->email);
            $this->info('Username: ' . $superAdmin->username);
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
