<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NavViewController extends Controller
{
    /**
     * Switch the sidebar between the user's role-specific nav and the full admin nav.
     */
    public function switch(Request $request, string $view)
    {
        $user = auth()->user();

        abort_unless($user && $user->isAdmin() && $user->getFunctionalNavRole(), 403);

        session(['nav_view' => $view === 'admin' ? 'admin' : 'role']);

        return redirect()->back();
    }
}
