<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeMaxDownlinesNullableInPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Use raw SQL to alter the column to nullable
        \DB::statement('ALTER TABLE `packages` MODIFY COLUMN `max_downlines` INT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Set default value for existing null records before making it non-nullable
        \DB::table('packages')->whereNull('max_downlines')->update(['max_downlines' => 2]);
        // Use raw SQL to alter the column back to non-nullable
        \DB::statement('ALTER TABLE `packages` MODIFY COLUMN `max_downlines` INT NOT NULL');
    }
}
