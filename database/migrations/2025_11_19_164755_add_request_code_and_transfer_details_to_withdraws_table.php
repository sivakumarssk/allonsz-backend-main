<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestCodeAndTransferDetailsToWithdrawsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if columns exist before adding them (safe for existing database)
        if (!Schema::hasColumn('withdraws', 'request_code')) {
            Schema::table('withdraws', function (Blueprint $table) {
                $table->string('request_code')->nullable()->after('amount');
            });
        }
        
        if (!Schema::hasColumn('withdraws', 'transfer_details')) {
            Schema::table('withdraws', function (Blueprint $table) {
                $table->text('transfer_details')->nullable()->after('ifsc_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('withdraws', function (Blueprint $table) {
            // Check if columns exist before dropping them
            if (Schema::hasColumn('withdraws', 'request_code')) {
                $table->dropColumn('request_code');
            }
            if (Schema::hasColumn('withdraws', 'transfer_details')) {
                $table->dropColumn('transfer_details');
            }
        });
    }
}
