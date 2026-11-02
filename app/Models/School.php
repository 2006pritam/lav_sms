<?php

namespace App\Models;

use Eloquent;

class School extends Eloquent
{
    protected $fillable = ['name', 'code', 'address', 'phone', 'email', 'active', 'disabled_modules'];

    protected $casts = ['disabled_modules' => 'array'];

    /**
     * Functions a super admin can switch on or off for each school.
     * Route-name prefixes decide which pages belong to which module.
     */
    public static $modules = [
        'students' => ['label' => 'Students & Promotion', 'routes' => ['students', 'st']],
        'attendance' => ['label' => 'Attendance', 'routes' => ['attendance']],
        'exams' => ['label' => 'Exams, Marks, Grades & Pins', 'routes' => ['exams', 'grades', 'marks', 'pins']],
        'payments' => ['label' => 'Fees & Payments', 'routes' => ['payments']],
        'timetables' => ['label' => 'Timetables', 'routes' => ['tt', 'ttr', 'ts']],
        'dorms' => ['label' => 'Dormitories', 'routes' => ['dorms']],
    ];

    public function moduleEnabled($key)
    {
        return !in_array($key, (array) $this->disabled_modules, true);
    }

    /** Module key that owns a route name, or null for always-on pages. */
    public static function moduleForRoute($routeName)
    {
        if (!$routeName) {
            return null;
        }
        $prefix = explode('.', $routeName)[0];
        foreach (self::$modules as $key => $m) {
            if (in_array($prefix, $m['routes'], true)) {
                return $key;
            }
        }

        return null;
    }

    /** Turn the checked boxes of a form into the list of switched-off modules. */
    public static function disabledFromInput($enabled)
    {
        $enabled = (array) $enabled;

        return array_values(array_diff(array_keys(self::$modules), $enabled));
    }
}
