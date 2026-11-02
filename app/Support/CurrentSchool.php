<?php

namespace App\Support;

use App\Models\School;

/**
 * Holds the school the current request is working in.
 *
 * It stays null for guests and for CLI work (migrations, seeders, artisan),
 * in which case no school filtering is applied.
 */
class CurrentSchool
{
    protected static $id = null;
    protected static $defaultId = false;
    protected static $model = null;
    protected static $platformAdmin = false;

    public static function id()
    {
        return static::$id;
    }

    public static function set($id)
    {
        static::$id = $id ? (int) $id : null;
    }

    public static function forget()
    {
        static::$id = null;
        static::$model = null;
        static::$platformAdmin = false;
    }

    public static function setPlatformAdmin($yes)
    {
        static::$platformAdmin = (bool) $yes;
    }

    public static function isPlatformAdmin()
    {
        return static::$platformAdmin;
    }

    public static function setModel($school)
    {
        static::$model = $school;
    }

    /** Is a function (module) switched on for the school being worked in? Guests/CLI: always yes. */
    public static function moduleEnabled($key)
    {
        return static::$model ? static::$model->moduleEnabled($key) : true;
    }

    /** Lowest-id active school; used for the login page and as a fallback. */
    public static function defaultId()
    {
        if (static::$defaultId === false) {
            try {
                $school = School::where('active', 1)->orderBy('id')->first();
                static::$defaultId = $school ? (int) $school->id : null;
            } catch (\Throwable $e) {
                static::$defaultId = null;
            }
        }

        return static::$defaultId;
    }

    public static function resetDefault()
    {
        static::$defaultId = false;
    }
}
