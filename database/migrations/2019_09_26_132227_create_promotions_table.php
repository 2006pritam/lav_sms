<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePromotionsTable extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('student_id');
                $table->unsignedInteger('from_class');
                $table->unsignedInteger('from_section');
                $table->unsignedInteger('to_class');
                $table->unsignedInteger('to_section');
                $table->tinyInteger('grad');
                $table->string('from_session');
                $table->string('to_session');
                $table->string('status');
                $table->timestamps();
            });
        }

        $fks = [
            ['student_id', 'id', 'users', 'cascade'],
            ['from_class', 'id', 'my_classes', 'cascade'],
            ['from_section', 'id', 'sections', 'cascade'],
            ['to_section', 'id', 'sections', 'cascade'],
            ['to_class', 'id', 'my_classes', 'cascade'],
        ];

        foreach ($fks as [$column, $ref, $on, $onDelete]) {
            try {
                Schema::table('promotions', function (Blueprint $table) use ($column, $ref, $on, $onDelete) {
                    $table->foreign($column)->references($ref)->on($on)->onDelete($onDelete);
                });
            } catch (\Throwable $e) {
                // Ignore duplicate constraint error
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('promotions');
    }
}
