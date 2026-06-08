<?php

namespace App\Http\Middleware;

use App\Models\Facility;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFacilitySetup
{
    private const EXEMPT_ROUTES = [
        'settings.index',
        'settings.facility.update',
        'facility.setup-required',
        'logout',
        'profile.edit',
        'profile.update',
        'profile.destroy',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (Facility::whereNotNull('name')->exists()) {
            return $next($request);
        }

        // Facility not set up — let exempt routes through
        if ($request->routeIs(...self::EXEMPT_ROUTES)) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && $user->isAdmin()) {
            return redirect()->route('settings.index')
                ->with('warning', 'Please complete your facility details before continuing.');
        }

        return redirect()->route('facility.setup-required');
    }
}
