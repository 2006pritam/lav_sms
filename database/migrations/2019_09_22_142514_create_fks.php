<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

require_once __DIR__ . '/2019_09_22_142513_repair_missing_lookup_tables.php';

class CreateFks extends Migration
{
    public $withinTransaction = false;

    public function up()
    {
        // Ensure all referenced and target tables exist even if an earlier migration batch was partial
        (new RepairMissingLookupTables())->up();

        $fks = [
            ['lgas', 'state_id', 'id', 'states', 'cascade'],
            ['users', 'state_id', 'id', 'states', 'set null'],
            ['users', 'lga_id', 'id', 'lgas', 'set null'],
            ['users', 'bg_id', 'id', 'blood_groups', 'set null'],
            ['users', 'nal_id', 'id', 'nationalities', 'set null'],
            ['my_classes', 'class_type_id', 'id', 'class_types', 'set null'],
            ['sections', 'my_class_id', 'id', 'my_classes', 'cascade'],
            ['sections', 'teacher_id', 'id', 'users', 'set null'],
            ['subjects', 'teacher_id', 'id', 'users', null],
            ['subjects', 'my_class_id', 'id', 'my_classes', 'cascade'],
            ['student_records', 'user_id', 'id', 'users', 'cascade'],
            ['student_records', 'my_class_id', 'id', 'my_classes', 'cascade'],
            ['student_records', 'section_id', 'id', 'sections', 'cascade'],
            ['student_records', 'my_parent_id', 'id', 'users', 'set null'],
            ['student_records', 'dorm_id', 'id', 'dorms', 'set null'],
            ['marks', 'student_id', 'id', 'users', 'cascade'],
            ['marks', 'my_class_id', 'id', 'my_classes', 'cascade'],
            ['marks', 'section_id', 'id', 'sections', 'cascade'],
            ['marks', 'subject_id', 'id', 'subjects', 'cascade'],
            ['marks', 'exam_id', 'id', 'exams', 'cascade'],
            ['marks', 'grade_id', 'id', 'grades', 'set null'],
            ['grades', 'class_type_id', 'id', 'class_types', 'cascade'],
            ['pins', 'user_id', 'id', 'users', 'cascade'],
            ['pins', 'student_id', 'id', 'users', 'cascade'],
            ['exam_records', 'exam_id', 'id', 'exams', 'cascade'],
            ['exam_records', 'my_class_id', 'id', 'my_classes', 'cascade'],
            ['exam_records', 'student_id', 'id', 'users', 'cascade'],
            ['exam_records', 'section_id', 'id', 'sections', 'cascade'],
            ['books', 'my_class_id', 'id', 'my_classes', 'cascade'],
            ['book_requests', 'book_id', 'id', 'books', 'cascade'],
            ['book_requests', 'user_id', 'id', 'users', 'cascade'],
            ['staff_records', 'user_id', 'id', 'users', 'cascade'],
            ['payments', 'my_class_id', 'id', 'my_classes', 'cascade'],
            ['payment_records', 'payment_id', 'id', 'payments', 'cascade'],
            ['payment_records', 'student_id', 'id', 'users', 'cascade'],
            ['receipts', 'pr_id', 'id', 'payment_records', 'cascade'],
            ['time_table_records', 'exam_id', 'id', 'exams', 'cascade'],
            ['time_table_records', 'my_class_id', 'id', 'my_classes', 'cascade'],
            ['time_slots', 'ttr_id', 'id', 'time_table_records', 'cascade'],
            ['time_tables', 'ttr_id', 'id', 'time_table_records', 'cascade'],
            ['time_tables', 'ts_id', 'id', 'time_slots', 'cascade'],
            ['time_tables', 'subject_id', 'id', 'subjects', 'cascade'],
        ];

        foreach ($fks as [$table, $column, $ref, $on, $onDelete]) {
            try {
                Schema::table($table, function (Blueprint $t) use ($column, $ref, $on, $onDelete) {
                    $fk = $t->foreign($column)->references($ref)->on($on);
                    if ($onDelete) {
                        $fk->onDelete($onDelete);
                    }
                });
            } catch (\Throwable $e) {
                // Ignore duplicate foreign key constraint errors on re-run
            }
        }
    }

    public function down()
    {
        //
    }
}
