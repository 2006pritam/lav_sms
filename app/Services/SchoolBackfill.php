<?php

namespace App\Services;

use App\Models\School;
use App\Support\CurrentSchool;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Makes sure a default school exists and that every school-owned row that has
 * no school yet (existing data, or rows inserted by the seeders) belongs to it.
 * Safe to run any number of times.
 */
class SchoolBackfill
{
    /** Tables whose rows belong to exactly one school. */
    public static $tenantTables = [
        'users', 'settings',
        'my_classes', 'sections', 'subjects', 'grades', 'dorms',
        'student_records', 'staff_records', 'promotions', 'attendances',
        'exams', 'exam_records', 'marks',
        'payments', 'payment_records', 'receipts', 'pins',
        'time_table_records', 'time_slots', 'time_tables',
        'books', 'book_requests',
    ];

    public static function run()
    {
        $default = static::ensureDefaultSchool();

        foreach (static::$tenantTables as $table) {
            if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'school_id')) {
                continue;
            }

            $query = DB::table($table)->whereNull('school_id');

            // Super admins run the whole platform, so they belong to no single school.
            if ($table === 'users') {
                $query->where('user_type', '<>', 'super_admin');
            }

            $query->update(['school_id' => $default->id]);
        }

        CurrentSchool::resetDefault();

        return $default;
    }

    public static function ensureDefaultSchool()
    {
        $school = School::orderBy('id')->first();

        if ($school) {
            return $school;
        }

        $setting = function ($type) {
            return Schema::hasTable('settings')
                ? DB::table('settings')->where('type', $type)->whereNull('school_id')->value('description')
                : null;
        };

        return School::create([
            'name' => $setting('system_name') ?: 'Main School',
            'code' => $setting('system_title') ?: 'MAIN',
            'address' => $setting('address'),
            'phone' => $setting('phone'),
            'email' => $setting('system_email'),
            'active' => 1,
        ]);
    }
}
