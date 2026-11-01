<?php

namespace App\Console\Commands;

use App\Services\SchoolBackfill as Backfill;
use Illuminate\Console\Command;

class SchoolBackfill extends Command
{
    protected $signature = 'school:backfill';

    protected $description = 'Create the default school and assign any rows without a school to it';

    public function handle()
    {
        $school = Backfill::run();
        $this->info("Default school: #{$school->id} {$school->name}");

        return 0;
    }
}
