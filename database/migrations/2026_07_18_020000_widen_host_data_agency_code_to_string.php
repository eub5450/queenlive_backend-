<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// doctrine/dbal is not installed on this app, so Schema::table(...)->change()
// is unavailable — raw ALTER TABLE via DB::statement() instead.
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE `host_data` MODIFY `agency_code` VARCHAR(255) NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `host_data` MODIFY `agency_code` INT(255) NOT NULL');
    }
};
