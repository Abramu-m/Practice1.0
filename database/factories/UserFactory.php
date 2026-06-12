<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    public function definition(): array
    {
        $genders = ['male', 'female', 'other'];
        $roles = ['user', 'admin', 'doctor', 'nurse', 'receptionist', 'cashier', 'pharmacist', 'lab_technician', 'super_admin'];
        
        $gender = $genders[array_rand($genders)];
        
        return [
            'first_name' => $this->faker->name,
            'middle_name' => $this->faker->name,
            'last_name' => $this->faker->name,
            'date_of_birth' => $this->faker->date('Y-m-d', '2005-01-01'),
            'gender' => $gender,
            'username' => $this->faker->unique()->userName,
            'phone' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'email' => $this->faker->unique()->safeEmail,
            'email_verified_at' => $this->faker->dateTime(),
            'profile_picture' => null, // Optional: fake image path
            'role' => $roles[array_rand($roles)],
            'is_active' => $this->faker->boolean(90),
            'is_verified' => $this->faker->boolean(70),
            'is_admin' => $this->faker->boolean(30),
            'is_super' => $this->faker->boolean(10),
            'password' => bcrypt('password'), // Or use Hash::make()
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}