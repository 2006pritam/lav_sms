<?php

use App\Services\SchoolBackfill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSchoolsAndAddSchoolId extends Migration
{
    /**
     * Unique rules that must become per-school: [table, old columns, new columns].
     */
    protected function uniqueSwaps()
    {
        return [
            ['my_classes', ['class_type_id', 'name'], ['school_id', 'class_type_id', 'name']],
            ['student_records', ['adm_no'], ['school_id', 'adm_no']],
            ['dorms', ['name'], ['school_id', 'name']],
            ['exams', ['term', 'year'], ['school_id', 'term', 'year']],
            ['grades', ['name', 'class_type_id', 'remark'], ['school_id', 'name', 'class_type_id', 'remark']],
            ['time_table_records', ['name'], ['school_id', 'name']],
        ];
    }

    public function up()
    {
        if (!Schema::hasTable('schools')) {
            Schema::create('schools', function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 150);
                $table->string('code', 40)->unique();
                $table->string('address')->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('email', 100)->nullable();
                $table->tinyInteger('active')->default(1);
                $table->timestamps();
            });
        }

        foreach (SchoolBackfill::$tenantTables as $name) {
            if (Schema::hasTable($name) && !Schema::hasColumn($name, 'school_id')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->unsignedInteger('school_id')->nullable()->index();
                });
            }
        }

        foreach ($this->uniqueSwaps() as [$name, $old, $new]) {
            if (!Schema::hasTable($name)) {
                continue;
            }

            $oldName = $name.'_'.implode('_', $old).'_unique';
            $newName = $name.'_'.implode('_', $new).'_unique';

            if ($this->indexExists($name, $oldName)) {
                Schema::table($name, function (Blueprint $table) use ($oldName) {
                    $table->dropUnique($oldName);
                });
            }

            if (!$this->indexExists($name, $newName)) {
                Schema::table($name, function (Blueprint $table) use ($new, $newName) {
                    $table->unique($new, $newName);
                });
            }
        }
    }

    public function down()
    {
        foreach ($this->uniqueSwaps() as [$name, $old, $new]) {
            if (!Schema::hasTable($name)) {
                continue;
            }
            $oldName = $name.'_'.implode('_', $old).'_unique';
            $newName = $name.'_'.implode('_', $new).'_unique';

            if ($this->indexExists($name, $newName)) {
                Schema::table($name, function (Blueprint $table) use ($newName) {
                    $table->dropUnique($newName);
                });
            }
            if (!$this->indexExists($name, $oldName)) {
                Schema::table($name, function (Blueprint $table) use ($old, $oldName) {
                    $table->unique($old, $oldName);
                });
            }
        }

        foreach (SchoolBackfill::$tenantTables as $name) {
            if (Schema::hasTable($name) && Schema::hasColumn($name, 'school_id')) {
                Schema::table($name, function (Blueprint $table) {
                    $table->dropIndex(['school_id']);
                    $table->dropColumn('school_id');
                });
            }
        }

        Schema::dropIfExists('schools');
    }

    protected function indexExists($table, $index)
    {
        $db = DB::connection();

        switch ($db->getDriverName()) {
            case 'pgsql':
                $sql = 'select 1 from pg_indexes where schemaname = current_schema() and tablename = ? and indexname = ?';
                break;
            case 'mysql':
                $sql = 'select 1 from information_schema.statistics where table_schema = database() and table_name = ? and index_name = ?';
                break;
            default: // sqlite
                $sql = "select 1 from sqlite_master where type = 'index' and tbl_name = ? and name = ?";
        }

        return !empty($db->select($sql, [$table, $index]));
    }
}
