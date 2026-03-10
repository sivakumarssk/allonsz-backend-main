<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComboFieldsToPackagesTable extends Migration
{
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'is_combo')) {
                $table->boolean('is_combo')->default(false)->after('status');
            }
            if (!Schema::hasColumn('packages', 'combo_five_reward_direct')) {
                $table->decimal('combo_five_reward_direct', 10, 2)->default(0)->after('is_combo');
            }
            if (!Schema::hasColumn('packages', 'combo_five_reward_first_autofill')) {
                $table->decimal('combo_five_reward_first_autofill', 10, 2)->default(0)->after('combo_five_reward_direct');
            }
            if (!Schema::hasColumn('packages', 'combo_five_reward_other_autofill')) {
                $table->decimal('combo_five_reward_other_autofill', 10, 2)->default(0)->after('combo_five_reward_first_autofill');
            }
            if (!Schema::hasColumn('packages', 'combo_five_autorenew_amount')) {
                $table->decimal('combo_five_autorenew_amount', 10, 2)->default(0)->after('combo_five_reward_other_autofill');
            }
            if (!Schema::hasColumn('packages', 'combo_twentyone_reward_amount')) {
                $table->decimal('combo_twentyone_reward_amount', 10, 2)->default(0)->after('combo_five_autorenew_amount');
            }
            if (!Schema::hasColumn('packages', 'combo_twentyone_autorenew_amount')) {
                $table->decimal('combo_twentyone_autorenew_amount', 10, 2)->default(0)->after('combo_twentyone_reward_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('packages', function (Blueprint $table) {
            if (Schema::hasColumn('packages', 'is_combo')) {
                $table->dropColumn('is_combo');
            }
            if (Schema::hasColumn('packages', 'combo_five_reward_direct')) {
                $table->dropColumn('combo_five_reward_direct');
            }
            if (Schema::hasColumn('packages', 'combo_five_reward_first_autofill')) {
                $table->dropColumn('combo_five_reward_first_autofill');
            }
            if (Schema::hasColumn('packages', 'combo_five_reward_other_autofill')) {
                $table->dropColumn('combo_five_reward_other_autofill');
            }
            if (Schema::hasColumn('packages', 'combo_five_autorenew_amount')) {
                $table->dropColumn('combo_five_autorenew_amount');
            }
            if (Schema::hasColumn('packages', 'combo_twentyone_reward_amount')) {
                $table->dropColumn('combo_twentyone_reward_amount');
            }
            if (Schema::hasColumn('packages', 'combo_twentyone_autorenew_amount')) {
                $table->dropColumn('combo_twentyone_autorenew_amount');
            }
        });
    }
}
