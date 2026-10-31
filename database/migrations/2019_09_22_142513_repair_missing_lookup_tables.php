<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RepairMissingLookupTables extends Migration
{
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
    }

    public function down()
    {
        // Keep repaired lookup tables on rollback to avoid deleting populated reference data.
    }
}
