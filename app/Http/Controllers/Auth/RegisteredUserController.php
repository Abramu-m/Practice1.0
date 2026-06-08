<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\NewUserRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Split the name into first and last name
        $nameParts = explode(' ', trim($request->name), 2);
        $firstName = $nameParts[0];
        $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

        // Generate a unique username
        $baseUsername = strtolower(str_replace(' ', '', $request->name));
        $username = $baseUsername;
        $counter = 1;
        
        // Ensure username is unique
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $request->email,
            'username' => $username,
            'role' => 'user', // Default role for registered users
            'is_verified' => false, // New users require admin verification
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        
        // Notify admins about new user registration
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        Notification::send($admins, new NewUserRegistered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
