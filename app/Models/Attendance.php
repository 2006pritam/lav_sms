<?php

namespace App\Models;

use App\User;
use Eloquent;

class Attendance extends Eloquent
{
    protected $fillable = [
        'student_id',
        'my_class_id',
        'section_id',
        'subject_id',
        'ttr_id',
        'ts_id',
        'tt_id',
        'teacher_id',
        'attendance_date',
        'status',
        'session',
        'remarks',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function my_class()
    {
        return $this->belongsTo(MyClass::class, 'my_class_id');
    }

    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function tt_record()
    {
        return $this->belongsTo(TimeTableRecord::class, 'ttr_id');
    }

    public function time_slot()
    {
        return $this->belongsTo(TimeSlot::class, 'ts_id');
    }

    public function time_table()
    {
        return $this->belongsTo(TimeTable::class, 'tt_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
