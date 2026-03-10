<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastAutofillIndexToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * This field tracks the index of the last downliner who received an autofill
     * from this user. Used for round-robin autofill distribution.
     *
     * Example: If user has downliners [A, B, C, D] (sorted by ID)
     * - last_combo_autofill_index = 0 means A was last, next should be B
     * - last_combo_autofill_index = 1 means B was last, next should be C
     * - last_combo_autofill_index = 3 means D was last, next should be A (wrap around)
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('last_combo_autofill_index')->default(0)->after('combo_wallet');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_combo_autofill_index');
        });
    }
}
