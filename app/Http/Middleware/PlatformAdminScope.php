<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * The super admin runs the platform only: schools, their status, their functions
 * and their first admins. Everything inside a school (students, staff, classes,
 * exams, fees ...) is managed by that school's own admin.
 */
class PlatformAdminScope
{
    protected $allowed = ['dashboard', 'home', 'my_account', 'my_account.*', 'schools.*', 'logout', 'privacy_policy', 'terms_of_use'];

    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->user_type === 'super_admin') {
            $route = optional($request->route());
            $name = $route->getName();

            if ($name && !$request->routeIs($this->allowed) && !$request->is('ajax/*')) {
                if ($request->expectsJson()) {
                    return response()->json(['ok' => false, 'msg' => 'School data is managed by each school\'s own admin.'], 403);
                }

                return redirect()->route('schools.index')
                    ->with('flash_danger', "School data is managed by each school's own admin. Super admin only manages schools.");
            }

            if (in_array($name, ['dashboard', 'home'], true)) {
                return redirect()->route('schools.index');
            }
        }

        return $next($request);
    }
}
