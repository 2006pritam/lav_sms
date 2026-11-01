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
