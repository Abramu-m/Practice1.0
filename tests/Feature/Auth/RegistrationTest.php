<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)->post('/register', [
        'first_name' => 'Test',
        'last_name' => 'User',
        'username' => 'testuser',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'nurse',
    ]);

    $this->assertAuthenticated();
    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'username' => 'testuser',
        'is_verified' => false,
    ]);
    $response->assertRedirect(route('custom.verification.notice', absolute: false));
});
