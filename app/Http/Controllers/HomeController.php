<?php

namespace App\Http\Controllers;

use App\Helpers\Qs;
use App\Http\Controllers\SupportTeam\AttendanceController;
use App\Models\MyClass;
use App\Repositories\UserRepo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class HomeController extends Controller
{
    protected $user;

    public function __construct(UserRepo $user)
    {
        $this->user = $user;
    }

    public function index()
    {
        return redirect()->route('dashboard');
    }

    public function privacy_policy()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Qs::getSetting('phone');
        return view('pages.other.privacy_policy', $data);
    }

    public function terms_of_use()
    {
        $data['app_name'] = config('app.name');
        $data['app_url'] = config('app.url');
        $data['contact_phone'] = Qs::getSetting('phone');
        return view('pages.other.terms_of_use', $data);
    }

    public function dashboard()
    {
        $d = [];
        if (Qs::userIsTeamSAT()) {
            $d['users'] = $this->user->getAll();
            $d['my_classes'] = MyClass::orderBy('name')->get();
        }

        if (Qs::userIsStudent() && Schema::hasTable('attendances')) {
            $attData = AttendanceController::buildStudentAttendanceData(
                Auth::id(),
                Qs::getCurrentSession()
            );
            $d = array_merge($d, $attData);
        }

        return view('pages.support_team.dashboard', $d);
    }
}
