<?php

namespace App\Traits;

use App\Models\School;
use App\Scopes\SchoolScope;
use App\Support\CurrentSchool;

/**
 * Makes a model school-specific: every query is limited to the current school
 * and new rows are stamped with it.
 *
 * A model may define:
 *   isSchoolExempt()          => true to leave school_id NULL on create
 *   includesGlobalSchoolRows() => true to also read rows with school_id NULL
 */
trait BelongsToSchool
{
    public static function bootBelongsToSchool()
    {
        static::addGlobalScope(new SchoolScope(method_exists(static::class, 'includesGlobalSchoolRows')));

        static::creating(function ($model) {
            if (!empty($model->school_id)) {
                return;
            }
            if (method_exists($model, 'isSchoolExempt') && $model->isSchoolExempt()) {
                return;
            }
            if ($id = CurrentSchool::id()) {
                $model->school_id = $id;
            }
        });
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }
}
