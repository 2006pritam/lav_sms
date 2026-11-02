<?php

namespace App\Scopes;

use App\Support\CurrentSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SchoolScope implements Scope
{
    protected $includeGlobalRows;

    /**
     * @param bool $includeGlobalRows also show rows whose school_id is NULL
     *                                (used for platform-wide super admin users)
     */
    public function __construct($includeGlobalRows = false)
    {
        $this->includeGlobalRows = $includeGlobalRows;
    }

    public function apply(Builder $builder, Model $model)
    {
        $school = CurrentSchool::id();

        if (!$school) {
            return;
        }

        $column = $model->getTable().'.school_id';

        if ($this->includeGlobalRows && CurrentSchool::isPlatformAdmin()) {
            $builder->where(function ($q) use ($column, $school) {
                $q->where($column, $school)->orWhereNull($column);
            });
        } else {
            $builder->where($column, $school);
        }
    }
}
