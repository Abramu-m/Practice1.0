<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use App\Notifications\NewUserRegistered;
use Illuminate\Support\Facades\Notification;

class UserController extends Controller
{
    public function index(Request $request)
    {   
        $search = $request->input('search');
        if ($search) {
            $users = User::with('doctor')
                ->where('first_name', 'like', '%' . $search . '%')
                ->orWhere('last_name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('username', 'like', '%' . $search . '%')
                ->paginate(10);
        } else {
            $users = User::with('doctor')->paginate(10);
        }

        return view('users.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::with('doctor')->findOrFail($id);

        return view('users.show', compact('user'));
    }

    public function create() 
    {
        return view('users.create');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    public function store(Request $request) 
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'min:8', 'confirmed', Password::defaults()],
            'username' => 'required|string|unique:users,username|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|max:2048',
            'role' => 'required|in:user,admin,doctor,nurse,receptionist,cashier,pharmacist,lab_technician,radiologist,super_admin',
            'is_active' => 'boolean',
        ]);

        $user = User::create([
            'first_name' => $request->first_name,
            'middle_name' => $request->middle_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'username' => $request->username,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'phone' => $request->phone,
            'address' => $request->address,
            'profile_picture' => $request->hasFile('profile_picture') ? $request->file('profile_picture')->store('profile_pictures', 'public') : null,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
            'is_verified' => false, // New users require admin verification
            'password' => Hash::make($request->password)
        ]);

        event(new Registered($user));
        
        // Notify admins about new user registration
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        Notification::send($admins, new NewUserRegistered($user));

        // Log the user in automatically after registration
        Auth::login($user);

        // Redirect directly to verification notice since new users need verification
        return redirect()->route('custom.verification.notice');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'username' => 'required|string|unique:users,username,' . $user->id . '|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|image|max:2048',
            'role' => 'required|in:user,admin,doctor,nurse,receptionist,cashier,pharmacist,lab_technician,radiologist,super_admin',
            'is_active' => 'boolean',
            'password' => ['nullable', 'string', 'min:8', 'confirmed', Password::defaults()],
        ]);

        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $path;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->fill($request->except(['password', 'profile_picture']));
        $user->save();
        
        return redirect()->route('users.index')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully');        
    }

    /**
     * Display users pending verification
     */
    public function pendingVerification()
    {
        $users = User::where('is_verified', false)
                    ->where('role', '!=', 'super_admin')
                    ->paginate(10);
        
        return view('users.pending-verification', compact('users'));
    }

    /**
     * Verify a user
     */
    public function verify($id)
    {
        $user = User::findOrFail($id);
        
        if ($user->is_verified) {
            return redirect()->back()->with('warning', 'User is already verified.');
        }

        $user->update([
            'is_verified' => true,
            'verified_at' => now()
        ]);

        return redirect()->back()->with('success', 'User verified successfully.');
    }

    /**
     * Unverify a user
     */
    public function unverify($id)
    {
        $user = User::findOrFail($id);
        
        if (!$user->is_verified) {
            return redirect()->back()->with('warning', 'User is already unverified.');
        }

        // Don't allow unverifying super admins
        if ($user->role === 'super_admin') {
            return redirect()->back()->with('error', 'Cannot unverify super admin.');
        }

        $user->update([
            'is_verified' => false,
            'verified_at' => null
        ]);

        return redirect()->back()->with('success', 'User verification revoked successfully.');
    }

    /**
     * Bulk verify users
     */
    public function bulkVerify(Request $request)
    {
        $userIds = $request->input('user_ids', []);
        
        if (empty($userIds)) {
            return redirect()->back()->with('warning', 'No users selected.');
        }

        $updated = User::whereIn('id', $userIds)
                      ->where('is_verified', false)
                      ->where('role', '!=', 'super_admin')
                      ->update([
                          'is_verified' => true,
                          'verified_at' => now()
                      ]);

        return redirect()->back()->with('success', "Successfully verified {$updated} user(s).");
    }
}
