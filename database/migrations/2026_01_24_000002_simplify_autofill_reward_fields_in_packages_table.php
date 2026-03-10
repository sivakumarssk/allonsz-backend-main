<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SimplifyAutofillRewardFieldsInPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Since we now only have ONE upliner OR ONE downliner for autofill (not multiple),
     * we no longer need separate first_autofill and other_autofill reward fields.
     *
     * This migration adds single autofill reward fields for each section.
     */
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            // Add single autofill reward fields
            $table->decimal('combo_five_a_reward_autofill', 10, 2)->nullable()->after('combo_five_a_reward_direct');
            $table->decimal('combo_five_b_reward_autofill', 10, 2)->nullable()->after('combo_five_b_reward_direct');
            $table->decimal('combo_five_c_reward_autofill', 10, 2)->nullable()->after('combo_five_c_reward_direct');
        });

        // Copy data from first_autofill to the new autofill field (if exists)
        \DB::statement('UPDATE packages SET combo_five_a_reward_autofill = combo_five_a_reward_first_autofill WHERE combo_five_a_reward_first_autofill IS NOT NULL');
        \DB::statement('UPDATE packages SET combo_five_b_reward_autofill = combo_five_b_reward_first_autofill WHERE combo_five_b_reward_first_autofill IS NOT NULL');
        \DB::statement('UPDATE packages SET combo_five_c_reward_autofill = combo_five_c_reward_first_autofill WHERE combo_five_c_reward_first_autofill IS NOT NULL');
    }

    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('combo_five_a_reward_autofill');
            $table->dropColumn('combo_five_b_reward_autofill');
            $table->dropColumn('combo_five_c_reward_autofill');
        });
    }
}
