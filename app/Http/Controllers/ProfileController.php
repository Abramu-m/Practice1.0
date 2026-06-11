<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $path;
        }
        
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Display the user's signature management page.
     */
    public function editSignature(Request $request): View
    {
        return view('profile.signature', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Save a drawn or uploaded signature for the user.
     */
    public function updateSignature(Request $request): RedirectResponse
    {
        $request->validate([
            'signature_data' => 'nullable|string',
            'signature_file' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();

        if ($user->signature) {
            Storage::disk('public')->delete($user->signature);
        }

        if ($request->filled('signature_data')) {
            $data = $request->input('signature_data');
            $data = Str::after($data, ',');
            $path = 'signatures/user_' . $user->id . '.png';
            Storage::disk('public')->put($path, base64_decode($data));
            $user->signature = $path;
        } elseif ($request->hasFile('signature_file')) {
            $extension = $request->file('signature_file')->getClientOriginalExtension();
            $path = $request->file('signature_file')->storeAs('signatures', 'user_' . $user->id . '.' . $extension, 'public');
            $user->signature = $path;
        } else {
            $user->signature = null;
        }

        $user->save();

        return Redirect::route('profile.signature.edit')->with('status', 'signature-updated');
    }

    /**
     * Remove the user's stored signature.
     */
    public function destroySignature(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->signature) {
            Storage::disk('public')->delete($user->signature);
            $user->signature = null;
            $user->save();
        }

        return Redirect::route('profile.signature.edit')->with('status', 'signature-removed');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
