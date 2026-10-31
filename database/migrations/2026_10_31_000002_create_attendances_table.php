<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttendancesTable extends Migration
{
    public $withinTransaction = false;

    public function up()
    {
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('student_id');
                $table->unsignedInteger('my_class_id');
                $table->unsignedInteger('section_id')->nullable();
                $table->unsignedInteger('subject_id')->nullable();
                $table->unsignedInteger('ttr_id')->nullable();
                $table->unsignedInteger('ts_id')->nullable();
                $table->unsignedInteger('tt_id')->nullable();
                $table->unsignedInteger('teacher_id')->nullable();
                $table->date('attendance_date');
                $table->string('status', 20)->default('present'); // 'present' or 'absent'
                $table->string('session', 40);
                $table->string('remarks')->nullable();
                $table->timestamps();

                $table->index(['student_id', 'session']);
                $table->index(['my_class_id', 'section_id', 'attendance_date']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('attendances');
    }
}
