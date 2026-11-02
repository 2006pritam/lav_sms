<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helpers\Qs;
use App\Http\Controllers\Controller;
use App\Models\School;
use App\Support\CurrentSchool;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SchoolController extends Controller
{
    public function index()
    {
        $d['schools'] = School::orderBy('id')->get();

        // Counted straight from the table so every school shows, whichever one is selected.
        $d['students'] = DB::table('users')->where('user_type', 'student')
            ->select('school_id', DB::raw('count(*) as total'))->groupBy('school_id')->pluck('total', 'school_id');
        $d['staff'] = DB::table('users')->whereNotIn('user_type', ['student', 'parent', 'super_admin'])
            ->select('school_id', DB::raw('count(*) as total'))->groupBy('school_id')->pluck('total', 'school_id');

        return view('pages.super_admin.schools.index', $d);
    }

    public function store(Request $req)
    {
        $req->validate([
            'name' => 'required|string|min:3|max:150',
            'code' => 'required|alpha_dash|max:40|unique:schools,code',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'admin_name' => 'required|string|max:100',
            'admin_username' => 'required|alpha_dash|min:3|max:100|unique:users,username',
            'admin_email' => 'required|email|max:100|unique:users,email',
            'admin_password' => 'required|string|min:6|max:100',
        ], [], [
            'code' => 'School Code',
            'admin_name' => 'Admin Name',
            'admin_username' => 'Admin Username',
            'admin_email' => 'Admin Email',
            'admin_password' => 'Admin Password',
        ]);

        $source = CurrentSchool::id() ?: CurrentSchool::defaultId();

        $school = DB::transaction(function () use ($req, $source) {
            $school = School::create([
                'name' => $req->name,
                'code' => strtoupper($req->code),
                'address' => $req->address,
                'phone' => $req->phone,
                'email' => $req->email,
                'active' => 1,
                'disabled_modules' => School::disabledFromInput($req->input('modules', array_keys(School::$modules))),
            ]);

            $this->createSettings($school, $source);

            if ($req->has('copy_defaults') && $source) {
                $this->copyClassesAndGrades($source, $school->id);
            }

            $admin = new User([
                'name' => ucwords($req->admin_name),
                'username' => $req->admin_username,
                'email' => $req->admin_email,
                'user_type' => 'admin',
                'code' => strtoupper(Str::random(10)),
                'photo' => Qs::getDefaultUserImage(),
                'password' => Hash::make($req->admin_password),
            ]);
            $admin->school_id = $school->id;
            $admin->save();

            return $school;
        });

        CurrentSchool::resetDefault();

        return redirect()->route('schools.index')
            ->with('flash_success', "School \"{$school->name}\" created. Its admin can sign in with the username and password you set.");
    }

    public function edit(School $school)
    {
        return view('pages.super_admin.schools.edit', ['school' => $school]);
    }

    public function update(Request $req, School $school)
    {
        $req->validate([
            'name' => 'required|string|min:3|max:150',
            'code' => 'required|alpha_dash|max:40|unique:schools,code,'.$school->id,
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
        ], [], ['code' => 'School Code']);

        $active = $req->has('active') ? 1 : 0;

        if (!$active && School::where('active', 1)->where('id', '<>', $school->id)->count() === 0) {
            return back()->withInput()->with('flash_danger', 'At least one school must stay active.');
        }

        $school->update([
            'name' => $req->name,
            'code' => strtoupper($req->code),
            'address' => $req->address,
            'phone' => $req->phone,
            'email' => $req->email,
            'active' => $active,
            'disabled_modules' => School::disabledFromInput($req->input('modules', [])),
        ]);

        // Keep the school's own settings page in step with these details.
        $map = [
            'system_name' => $school->name,
            'system_title' => $school->code,
            'address' => $school->address,
            'phone' => $school->phone,
            'system_email' => $school->email,
        ];
        foreach ($map as $type => $value) {
            DB::table('settings')->updateOrInsert(
                ['school_id' => $school->id, 'type' => $type],
                ['description' => $value, 'updated_at' => now()]
            );
        }

        CurrentSchool::resetDefault();

        return redirect()->route('schools.index')->with('flash_success', 'School updated.');
    }

    /**
     * Permanently remove a school and everything that belongs to it.
     * The school code must be typed to confirm; the last school can never be removed.
     */
    public function destroy(Request $req, School $school)
    {
        $req->validate(['confirm_code' => 'required|string']);

        if (strcasecmp(trim($req->confirm_code), $school->code) !== 0) {
            return back()->with('flash_danger', 'The code you typed does not match, so nothing was deleted.');
        }

        if (School::where('id', '<>', $school->id)->count() === 0) {
            return back()->with('flash_danger', 'The last school cannot be deleted.');
        }

        if ($school->active && School::where('active', 1)->where('id', '<>', $school->id)->count() === 0) {
            return back()->with('flash_danger', 'Activate another school before deleting the only active one.');
        }

        $name = $school->name;

        try {
            DB::transaction(function () use ($school) {
                $this->purgeSchoolData($school->id);
                $school->delete();
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->with('flash_danger', 'The school could not be deleted, so nothing was changed.');
        }

        if ((int) $req->session()->get('school_id') === (int) $school->id) {
            $req->session()->forget('school_id');
        }
        CurrentSchool::resetDefault();

        // The school's own uploaded files (logo etc.).
        try {
            \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory('uploads/school_'.$school->id);
        } catch (\Throwable $e) {
        }

        return redirect()->route('schools.index')->with('flash_success', "School \"{$name}\" and all its data were deleted.");
    }

    /**
     * Delete every row of a school. Tables are tried repeatedly so that rows other
     * rows depend on go last, whatever the foreign keys look like.
     */
    protected function purgeSchoolData($id)
    {
        $pending = array_reverse(\App\Services\SchoolBackfill::$tenantTables);

        for ($pass = 0; $pass < 8 && $pending; $pass++) {
            $failed = [];
            foreach ($pending as $table) {
                try {
                    DB::transaction(function () use ($table, $id) {
                        DB::table($table)->where('school_id', $id)->delete();
                    });
                } catch (\Throwable $e) {
                    $failed[$table] = $e;
                }
            }
            $pending = array_keys($failed);
        }

        if ($pending) {
            throw reset($failed);
        }
    }

    /** One-click activate / deactivate from the schools list. */
    public function toggle(School $school)
    {
        if ($school->active && School::where('active', 1)->where('id', '<>', $school->id)->count() === 0) {
            return back()->with('flash_danger', 'At least one school must stay active.');
        }

        $school->update(['active' => $school->active ? 0 : 1]);
        CurrentSchool::resetDefault();

        return back()->with('flash_success', $school->active ? 'School activated.' : 'School deactivated. Its users can no longer sign in.');
    }

    public function switch(Request $req, School $school)
    {
        $req->session()->put('school_id', $school->id);

        return redirect()->route('dashboard')->with('flash_success', "You are now working in {$school->name}.");
    }

    /** Start the new school off with a copy of an existing school's settings. */
    protected function createSettings(School $school, $source)
    {
        $existing = $source
            ? DB::table('settings')->where('school_id', $source)->pluck('description', 'type')->toArray()
            : [];

        $settings = array_merge(Qs::getDefaultSettings(), $existing, [
            'system_name' => $school->name,
            'system_title' => $school->code,
            'address' => (string) $school->address,
            'phone' => (string) $school->phone,
            'system_email' => (string) $school->email,
            'logo' => '',
        ]);

        $rows = [];
        foreach ($settings as $type => $description) {
            $rows[] = [
                'school_id' => $school->id,
                'type' => $type,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('settings')->insert($rows);
    }

    protected function copyClassesAndGrades($from, $to)
    {
        $classes = DB::table('my_classes')->where('school_id', $from)->get(['name', 'class_type_id']);
        foreach ($classes as $c) {
            DB::table('my_classes')->insert([
                'name' => $c->name,
                'class_type_id' => $c->class_type_id,
                'school_id' => $to,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $grades = DB::table('grades')->where('school_id', $from)->get(['name', 'class_type_id', 'mark_from', 'mark_to', 'remark']);
        foreach ($grades as $g) {
            DB::table('grades')->insert([
                'name' => $g->name,
                'class_type_id' => $g->class_type_id,
                'mark_from' => $g->mark_from,
                'mark_to' => $g->mark_to,
                'remark' => $g->remark,
                'school_id' => $to,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
