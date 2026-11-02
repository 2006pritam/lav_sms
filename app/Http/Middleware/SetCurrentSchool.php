<?php

namespace App\Http\Middleware;

use App\Models\School;
use App\Support\CurrentSchool;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

/**
 * Decides which school the signed-in user is working in.
 *
 *  - Everyone except super admins works in their own school.
 *  - Super admins work in the school they picked with the school switcher.
 *  - Guests (the login page) are not tied to any school.
 */
class SetCurrentSchool
{
    public function handle($request, Closure $next)
    {
        CurrentSchool::forget();

        $school = null;
        $schools = collect();

        if (Auth::check()) {
            $user = Auth::user();
            $isSuperAdmin = $user->user_type === 'super_admin';

            if ($isSuperAdmin) {
                $chosen = $request->session()->get('school_id');
                $school = $chosen ? School::find($chosen) : null;
                $schools = School::orderBy('name')->get();
            } elseif ($user->school_id) {
                $school = School::find($user->school_id);
            }

            if (!$school) {
                $default = CurrentSchool::defaultId();
                $school = $default ? School::find($default) : null;
            }

            if ($school && !$school->active && !$isSuperAdmin) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'This school is currently inactive. Please contact the administrator.']);
            }

            if ($school) {
                CurrentSchool::set($school->id);
                CurrentSchool::setModel($school);
            }
        }

        View::share('currentSchool', $school);
        View::share('switchableSchools', $schools);

        return $next($request);
    }
}
