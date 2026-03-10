<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSectionNamesToPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages', 'combo_five_a_name')) {
                $table->string('combo_five_a_name')->nullable()->after('combo_twentyone_autorenew_amount');
            }
            if (!Schema::hasColumn('packages', 'combo_five_b_name')) {
                $table->string('combo_five_b_name')->nullable()->after('combo_five_a_name');
            }
            if (!Schema::hasColumn('packages', 'combo_twentyone_name')) {
                $table->string('combo_twentyone_name')->nullable()->after('combo_five_b_name');
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
            if (Schema::hasColumn('packages', 'combo_five_a_name')) {
                $table->dropColumn('combo_five_a_name');
            }
            if (Schema::hasColumn('packages', 'combo_five_b_name')) {
                $table->dropColumn('combo_five_b_name');
            }
            if (Schema::hasColumn('packages', 'combo_twentyone_name')) {
                $table->dropColumn('combo_twentyone_name');
            }
        });
    }
}
