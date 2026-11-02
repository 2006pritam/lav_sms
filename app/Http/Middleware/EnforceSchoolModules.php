<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Support\CurrentSchool;
use Closure;
use Illuminate\Support\Facades\Auth;

/**
 * Blocks pages that belong to a function the super admin switched off
 * for the school the user is working in.
 */
class EnforceSchoolModules
{
    public function handle($request, Closure $next)
    {
        if (Auth::check()) {
            $module = School::moduleForRoute(optional($request->route())->getName());

            if ($module && !CurrentSchool::moduleEnabled($module)) {
                $label = School::$modules[$module]['label'];
                $message = "{$label} is switched off for this school.";

                if ($request->expectsJson()) {
                    return response()->json(['ok' => false, 'msg' => $message], 403);
                }

                return redirect()->route('dashboard')->with('flash_danger', $message);
            }
        }

        return $next($request);
    }
}
