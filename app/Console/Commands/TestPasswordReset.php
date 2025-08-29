<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Mail\GenericMail;

class TestPasswordReset extends Command
{
    protected $signature = 'password:test-reset {email=reset-test@example.com} {--attach=}
        ';

    protected $description = 'Create test user if needed, create password reset token, log the reset URL, and send reset email.';

    public function handle()
    {
        $email = $this->argument('email');
        $attach = $this->option('attach');

        // Ensure user exists
        $user = User::where('email', $email)->first();
        if (!$user) {
            $password = 'Password123!';
            $user = User::create([
                'name' => 'Password Reset Test',
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);
            $this->info("Created test user {$email} with password: {$password}");
        } else {
            $this->info("Using existing user {$email}");
        }

        // Create token using Password broker
        $token = Password::createToken($user);

        // Construct reset URL using named route if available, else use typical path
        try {
            $resetUrl = url(route('password.reset', ['token' => $token, 'email' => $user->email], false));
        } catch (\Exception $e) {
            $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($user->email));
        }

        // Log the reset URL so operator can inspect where it was sent
        Log::info('Password reset URL generated for test user', ['email' => $user->email, 'url' => $resetUrl]);
        $this->info('Password reset URL: ' . $resetUrl);

        // Send the reset link via mail using GenericMail
        $subject = 'Test: Password Reset Link';
        $body = "<p>Hello,</p><p>This is a test password reset link generated at " . now() . "</p>";
        $body .= "<p><a href=\"{$resetUrl}\">Click here to reset password</a></p>";

        $attachments = [];
        if ($attach) {
            // allow comma-separated list
            $attachments = array_filter(array_map('trim', explode(',', $attach)));
        }

        try {
            Mail::to($user->email)->send(new GenericMail($subject, $body, $attachments));
            $this->info('Reset link emailed to ' . $user->email);
        } catch (\Exception $e) {
            $this->error('Failed to send reset email: ' . $e->getMessage());
        }

        return 0;
    }
}
