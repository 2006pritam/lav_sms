<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class AddDisabledModulesToSchools extends Migration
{
    public function up()
    {
        if (Schema::hasTable('schools') && !Schema::hasColumn('schools', 'disabled_modules')) {
            Schema::table('schools', function ($table) {
                // JSON list of module keys the super admin switched off for this school.
                $table->text('disabled_modules')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('schools', 'disabled_modules')) {
            Schema::table('schools', function ($table) {
                $table->dropColumn('disabled_modules');
            });
        }
    }
}
