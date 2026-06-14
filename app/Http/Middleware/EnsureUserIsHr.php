<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsHr
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = \Illuminate\Support\Facades\Auth::user();

        if (!$user) {
            abort(403, 'Access denied. HR privileges required.');
        }

        if ($user->is_admin || $user->is_super || $user->role === 'hr') {
            return $next($request);
        }

        // Cashiers only get access to the Salary Payments section of HR.
        if ($user->isCashier() && str_starts_with($request->route()?->getName() ?? '', 'hr.salary-payments.')) {
            return $next($request);
        }

        abort(403, 'Access denied. HR privileges required.');
    }
}
