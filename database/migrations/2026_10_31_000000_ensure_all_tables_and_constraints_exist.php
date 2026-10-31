<?php

use Illuminate\Database\Migrations\Migration;

require_once __DIR__ . '/2019_09_22_142513_repair_missing_lookup_tables.php';
require_once __DIR__ . '/2019_09_22_142514_create_fks.php';
require_once __DIR__ . '/2019_09_26_132227_create_promotions_table.php';

class EnsureAllTablesAndConstraintsExist extends Migration
{
    public $withinTransaction = false;

    public function up()
    {
        (new RepairMissingLookupTables())->up();
        (new CreateFks())->up();
        (new CreatePromotionsTable())->up();
    }

    public function down()
    {
        //
    }
}
