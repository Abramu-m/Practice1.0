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

    /**
     * Show a simple form for admin to trigger password reset for a user.
     */
    public function passwordResetForm()
    {
        $users = User::select('id', 'first_name', 'last_name', 'email')->orderBy('first_name')->get();
        return view('users.password-reset', compact('users'));
    }

    /**
     * Show form to admin for setting a new password for a specific user.
     */
    public function adminResetForm($id)
    {
        $user = User::findOrFail($id);
        return view('users.admin-reset-password', compact('user'));
    }

    /**
     * Send password reset link to a selected user (admin action).
     */
    public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'note' => ['nullable', 'string', 'max:1000'],
            'cc_admins' => ['nullable', 'boolean']
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return redirect()->back()->withErrors(['email' => 'No user found with that email address.']);
        }

        // Create audit record
        $admin = Auth::user();
        $audit = \App\Models\PasswordResetRequest::create([
            'admin_id' => $admin ? $admin->id : null,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'status' => 'pending',
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'note' => $request->input('note')
        ]);

        $status = \Illuminate\Support\Facades\Password::sendResetLink(
            $request->only('email')
        );

        if ($status == \Illuminate\Support\Facades\Password::RESET_LINK_SENT) {
            $audit->update(['status' => 'sent', 'sent_at' => now()]);

            // Optionally notify other admins that admin triggered this
            if ($request->boolean('cc_admins')) {
                $admins = User::whereIn('role', ['admin', 'super_admin'])->where('id', '!=', $admin->id)->get();
                if ($admins->isNotEmpty()) {
                    \Illuminate\Support\Facades\Notification::send($admins, new \App\Notifications\AdminTriggeredPasswordReset($admin, $user->email));
                }
            }

            return redirect()->route('users.index')->with('success', 'Password reset link sent to user.');
        }

        $audit->update(['status' => 'failed']);
        return redirect()->back()->withErrors(['email' => __($status)]);
    }

    /**
     * Admin sets a new password for a user directly.
     */
    public function adminResetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed']
        ]);

        $admin = Auth::user();

        $user->password = Hash::make($request->password);
        $user->save();

        // Record audit
        \App\Models\PasswordResetRequest::create([
            'admin_id' => $admin ? $admin->id : null,
            'user_id' => $user->id,
            'user_email' => $user->email,
            'status' => 'admin_reset',
            'sent_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'note' => $request->input('note')
        ]);

        // Optionally notify the user that their password was changed by admin
        if ($request->boolean('notify_user')) {
            try {
                $user->notify(new \App\Notifications\UserPasswordChangedByAdmin($admin));
            } catch (\Throwable $e) {
                // don't block on notification failure
            }
        }

        // Optionally notify other admins
        if ($request->boolean('cc_admins')) {
            $admins = User::whereIn('role', ['admin', 'super_admin'])->where('id', '!=', $admin->id)->get();
            if ($admins->isNotEmpty()) {
                try {
                    Notification::send($admins, new \App\Notifications\AdminTriggeredPasswordReset($admin, $user->email));
                } catch (\Throwable $e) {
                    // don't block on notification failure
                }
            }
        }

        return redirect()->route('users.show', $user->id)->with('success', 'User password updated successfully.');
    }
}
