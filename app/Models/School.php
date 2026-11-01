<?php

namespace App\Models;

use Eloquent;

class School extends Eloquent
{
    protected $fillable = ['name', 'code', 'address', 'phone', 'email', 'active'];
}
