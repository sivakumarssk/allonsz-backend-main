<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SeparateFiveAFiveBRewardFieldsInPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            // Add separate reward fields for five_a circle
            if (!Schema::hasColumn('packages', 'combo_five_a_reward_direct')) {
                $table->decimal('combo_five_a_reward_direct', 10, 2)->default(0)->after('combo_twentyone_name');
            }
            if (!Schema::hasColumn('packages', 'combo_five_a_reward_first_autofill')) {
                $table->decimal('combo_five_a_reward_first_autofill', 10, 2)->default(0)->after('combo_five_a_reward_direct');
            }
            if (!Schema::hasColumn('packages', 'combo_five_a_reward_other_autofill')) {
                $table->decimal('combo_five_a_reward_other_autofill', 10, 2)->default(0)->after('combo_five_a_reward_first_autofill');
            }
            if (!Schema::hasColumn('packages', 'combo_five_a_autorenew_amount')) {
                $table->decimal('combo_five_a_autorenew_amount', 10, 2)->default(0)->after('combo_five_a_reward_other_autofill');
            }
            
            // Add separate reward fields for five_b circle
            if (!Schema::hasColumn('packages', 'combo_five_b_reward_direct')) {
                $table->decimal('combo_five_b_reward_direct', 10, 2)->default(0)->after('combo_five_a_autorenew_amount');
            }
            if (!Schema::hasColumn('packages', 'combo_five_b_reward_first_autofill')) {
                $table->decimal('combo_five_b_reward_first_autofill', 10, 2)->default(0)->after('combo_five_b_reward_direct');
            }
            if (!Schema::hasColumn('packages', 'combo_five_b_reward_other_autofill')) {
                $table->decimal('combo_five_b_reward_other_autofill', 10, 2)->default(0)->after('combo_five_b_reward_first_autofill');
            }
            if (!Schema::hasColumn('packages', 'combo_five_b_autorenew_amount')) {
                $table->decimal('combo_five_b_autorenew_amount', 10, 2)->default(0)->after('combo_five_b_reward_other_autofill');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            // Remove five_a reward fields
            if (Schema::hasColumn('packages', 'combo_five_a_reward_direct')) {
                $table->dropColumn('combo_five_a_reward_direct');
            }
            if (Schema::hasColumn('packages', 'combo_five_a_reward_first_autofill')) {
                $table->dropColumn('combo_five_a_reward_first_autofill');
            }
            if (Schema::hasColumn('packages', 'combo_five_a_reward_other_autofill')) {
                $table->dropColumn('combo_five_a_reward_other_autofill');
            }
            if (Schema::hasColumn('packages', 'combo_five_a_autorenew_amount')) {
                $table->dropColumn('combo_five_a_autorenew_amount');
            }
            
            // Remove five_b reward fields
            if (Schema::hasColumn('packages', 'combo_five_b_reward_direct')) {
                $table->dropColumn('combo_five_b_reward_direct');
            }
            if (Schema::hasColumn('packages', 'combo_five_b_reward_first_autofill')) {
                $table->dropColumn('combo_five_b_reward_first_autofill');
            }
            if (Schema::hasColumn('packages', 'combo_five_b_reward_other_autofill')) {
                $table->dropColumn('combo_five_b_reward_other_autofill');
            }
            if (Schema::hasColumn('packages', 'combo_five_b_autorenew_amount')) {
                $table->dropColumn('combo_five_b_autorenew_amount');
            }
        });
    }
}
