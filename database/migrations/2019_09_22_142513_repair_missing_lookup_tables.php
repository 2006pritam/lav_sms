<?php

use App\Helpers\Qs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RepairMissingLookupTables extends Migration
{
    public $withinTransaction = false;

    public function up()
    {
        if (!Schema::hasTable('blood_groups')) {
            Schema::create('blood_groups', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('states')) {
            Schema::create('states', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('lgas')) {
            Schema::create('lgas', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('state_id');
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('nationalities')) {
            Schema::create('nationalities', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('email', 100)->unique()->nullable();
                $table->string('code', 100)->unique();
                $table->string('username', 100)->nullable()->unique();
                $table->string('user_type');
                $table->string('dob')->nullable();
                $table->string('gender')->nullable();
                $table->string('photo')->default(Qs::getDefaultUserImage());
                $table->string('phone')->nullable();
                $table->string('phone2')->nullable();
                $table->unsignedInteger('bg_id')->nullable();
                $table->unsignedInteger('state_id')->nullable();
                $table->unsignedInteger('lga_id')->nullable();
                $table->unsignedInteger('nal_id')->nullable();
                $table->string('address')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('password_resets')) {
            Schema::create('password_resets', function (Blueprint $table) {
                $table->string('email', 100)->index();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('user_types')) {
            Schema::create('user_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title');
                $table->string('name');
                $table->string('level');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('class_types')) {
            Schema::create('class_types', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('code');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('my_classes')) {
            Schema::create('my_classes', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100);
                $table->unsignedInteger('class_type_id')->nullable();
                $table->timestamps();
                $table->unique(['class_type_id', 'name']);
            });
        }

        if (!Schema::hasTable('sections')) {
            Schema::create('sections', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100);
                $table->unsignedInteger('my_class_id');
                $table->unsignedInteger('teacher_id')->nullable();
                $table->tinyInteger('active')->default(0);
                $table->timestamps();
                $table->unique(['name', 'my_class_id']);
            });
        }

        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('subjects')) {
            Schema::create('subjects', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100);
                $table->string('slug', 100);
                $table->unsignedInteger('my_class_id');
                $table->unsignedInteger('teacher_id');
                $table->timestamps();
                $table->unique(['my_class_id', 'name']);
            });
        }

        if (!Schema::hasTable('student_records')) {
            Schema::create('student_records', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('my_class_id');
                $table->unsignedInteger('section_id');
                $table->string('adm_no', 30)->unique()->nullable();
                $table->unsignedInteger('my_parent_id')->nullable();
                $table->unsignedInteger('dorm_id')->nullable();
                $table->string('dorm_room_no')->nullable();
                $table->string('session');
                $table->string('house')->nullable();
                $table->tinyInteger('age')->nullable();
                $table->string('year_admitted')->nullable();
                $table->tinyInteger('grad')->default(0);
                $table->string('grad_date')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('dorms')) {
            Schema::create('dorms', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100)->unique();
                $table->string('description')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('exams')) {
            Schema::create('exams', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->tinyInteger('term');
                $table->string('year', 40);
                $table->timestamps();
                $table->unique(['term', 'year']);
            });
        }

        if (!Schema::hasTable('marks')) {
            Schema::create('marks', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('student_id');
                $table->unsignedInteger('subject_id');
                $table->unsignedInteger('my_class_id');
                $table->unsignedInteger('section_id');
                $table->unsignedInteger('exam_id');
                $table->integer('t1')->nullable();
                $table->integer('t2')->nullable();
                $table->integer('t3')->nullable();
                $table->integer('t4')->nullable();
                $table->integer('tca')->nullable();
                $table->integer('exm')->nullable();
                $table->integer('tex1')->nullable();
                $table->integer('tex2')->nullable();
                $table->integer('tex3')->nullable();
                $table->tinyInteger('sub_pos')->nullable();
                $table->integer('cum')->nullable();
                $table->string('cum_ave')->nullable();
                $table->unsignedInteger('grade_id')->nullable();
                $table->string('year');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('grades')) {
            Schema::create('grades', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 40);
                $table->unsignedInteger('class_type_id')->nullable();
                $table->tinyInteger('mark_from');
                $table->tinyInteger('mark_to');
                $table->string('remark', 40)->nullable();
                $table->timestamps();
                $table->unique(['name', 'class_type_id', 'remark']);
            });
        }

        if (!Schema::hasTable('pins')) {
            Schema::create('pins', function (Blueprint $table) {
                $table->increments('id');
                $table->string('code', 40)->unique();
                $table->string('used')->default(0);
                $table->string('times_used')->default(0);
                $table->unsignedInteger('user_id')->nullable();
                $table->unsignedInteger('student_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('skills')) {
            Schema::create('skills', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name');
                $table->string('skill_type');
                $table->string('class_type')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('exam_records')) {
            Schema::create('exam_records', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('exam_id');
                $table->unsignedInteger('student_id');
                $table->unsignedInteger('my_class_id');
                $table->unsignedInteger('section_id');
                $table->integer('total')->nullable();
                $table->string('ave')->nullable();
                $table->string('class_ave')->nullable();
                $table->integer('pos')->nullable();
                $table->string('af')->nullable();
                $table->string('ps')->nullable();
                $table->string('p_comment')->nullable();
                $table->string('t_comment')->nullable();
                $table->string('year');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('books')) {
            Schema::create('books', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100);
                $table->unsignedInteger('my_class_id')->nullable();
                $table->string('description')->nullable();
                $table->string('author')->nullable();
                $table->string('book_type')->nullable();
                $table->string('url')->nullable();
                $table->string('location')->nullable();
                $table->integer('total_copies')->nullable();
                $table->integer('issued_copies')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('book_requests')) {
            Schema::create('book_requests', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('book_id');
                $table->unsignedInteger('user_id');
                $table->string('start_date');
                $table->string('end_date');
                $table->string('returned')->default(0);
                $table->string('status')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('staff_records')) {
            Schema::create('staff_records', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('user_id');
                $table->string('code', 100)->nullable()->unique();
                $table->string('emp_date')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->increments('id');
                $table->string('title', 100);
                $table->integer('amount');
                $table->string('ref_no', 100)->unique();
                $table->string('method', 100)->default('cash');
                $table->unsignedInteger('my_class_id')->nullable();
                $table->string('description')->nullable();
                $table->string('year');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payment_records')) {
            Schema::create('payment_records', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('payment_id');
                $table->unsignedInteger('student_id');
                $table->string('ref_no', 100)->unique()->nullable();
                $table->integer('amt_paid')->nullable();
                $table->integer('balance')->nullable();
                $table->tinyInteger('paid')->default(0);
                $table->string('year');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('receipts')) {
            Schema::create('receipts', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('pr_id');
                $table->integer('amt_paid');
                $table->integer('balance');
                $table->string('year');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('time_table_records')) {
            Schema::create('time_table_records', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 100)->unique();
                $table->unsignedInteger('my_class_id');
                $table->unsignedInteger('exam_id')->nullable();
                $table->string('year', 100);
                $table->timestamps();
                $table->unique(['my_class_id', 'exam_id', 'year']);
            });
        }

        if (!Schema::hasTable('time_slots')) {
            Schema::create('time_slots', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('ttr_id');
                $table->tinyInteger('hour_from');
                $table->string('min_from', 2);
                $table->string('meridian_from', 2);
                $table->tinyInteger('hour_to');
                $table->string('min_to', 2);
                $table->string('meridian_to', 2);
                $table->string('time_from', 100);
                $table->string('time_to', 100);
                $table->string('timestamp_from', 50);
                $table->string('timestamp_to', 50);
                $table->string('full', 100);
                $table->timestamps();
                $table->unique(['timestamp_from', 'timestamp_to', 'ttr_id']);
            });
        }

        if (!Schema::hasTable('time_tables')) {
            Schema::create('time_tables', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('ttr_id');
                $table->unsignedInteger('ts_id');
                $table->unsignedInteger('subject_id')->nullable();
                $table->string('exam_date', 50)->nullable();
                $table->string('timestamp_from', 100);
                $table->string('timestamp_to', 100);
                $table->string('day', 50)->nullable();
                $table->tinyInteger('day_num')->nullable();
                $table->timestamps();
                $table->unique(['ttr_id', 'ts_id', 'day']);
                $table->unique(['ttr_id', 'ts_id', 'exam_date']);
            });
        }
    }

    public function down()
    {
        // Keep repaired lookup and core tables on rollback to avoid deleting populated data.
    }
}
