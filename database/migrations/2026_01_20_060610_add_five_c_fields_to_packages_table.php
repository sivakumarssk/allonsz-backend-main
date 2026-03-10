<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFiveCFieldsToPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            // Add section name for five_c
            if (!Schema::hasColumn('packages', 'combo_five_c_name')) {
                $table->string('combo_five_c_name')->nullable()->after('combo_five_b_autorenew_amount');
            }
            
            // Add separate reward fields for five_c circle
            if (!Schema::hasColumn('packages', 'combo_five_c_reward_direct')) {
                $table->decimal('combo_five_c_reward_direct', 10, 2)->default(0)->after('combo_five_c_name');
            }
            if (!Schema::hasColumn('packages', 'combo_five_c_reward_first_autofill')) {
                $table->decimal('combo_five_c_reward_first_autofill', 10, 2)->default(0)->after('combo_five_c_reward_direct');
            }
            if (!Schema::hasColumn('packages', 'combo_five_c_reward_other_autofill')) {
                $table->decimal('combo_five_c_reward_other_autofill', 10, 2)->default(0)->after('combo_five_c_reward_first_autofill');
            }
            if (!Schema::hasColumn('packages', 'combo_five_c_autorenew_amount')) {
                $table->decimal('combo_five_c_autorenew_amount', 10, 2)->default(0)->after('combo_five_c_reward_other_autofill');
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
            if (Schema::hasColumn('packages', 'combo_five_c_name')) {
                $table->dropColumn('combo_five_c_name');
            }
            if (Schema::hasColumn('packages', 'combo_five_c_reward_direct')) {
                $table->dropColumn('combo_five_c_reward_direct');
            }
            if (Schema::hasColumn('packages', 'combo_five_c_reward_first_autofill')) {
                $table->dropColumn('combo_five_c_reward_first_autofill');
            }
            if (Schema::hasColumn('packages', 'combo_five_c_reward_other_autofill')) {
                $table->dropColumn('combo_five_c_reward_other_autofill');
            }
            if (Schema::hasColumn('packages', 'combo_five_c_autorenew_amount')) {
                $table->dropColumn('combo_five_c_autorenew_amount');
            }
        });
    }
}
