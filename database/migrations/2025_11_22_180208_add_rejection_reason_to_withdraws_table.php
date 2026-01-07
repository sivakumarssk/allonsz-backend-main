<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRejectionReasonToWithdrawsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('withdraws', function (Blueprint $table) {
            if (!Schema::hasColumn('withdraws', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('transfer_details');
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
        Schema::table('withdraws', function (Blueprint $table) {
            if (Schema::hasColumn('withdraws', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
}
