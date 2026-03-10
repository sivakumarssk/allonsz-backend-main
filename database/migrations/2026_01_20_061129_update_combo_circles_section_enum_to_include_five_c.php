<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateComboCirclesSectionEnumToIncludeFiveC extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update the enum to include 'five_c'
        DB::statement("ALTER TABLE combo_circles MODIFY COLUMN section ENUM('five_a', 'five_b', 'five_c', 'twentyone') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to original enum (without five_c)
        DB::statement("ALTER TABLE combo_circles MODIFY COLUMN section ENUM('five_a', 'five_b', 'twentyone') NOT NULL");
    }
}
