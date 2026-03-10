<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWalletTypeToWithdrawsTable extends Migration
{
    public function up()
    {
        Schema::table('withdraws', function (Blueprint $table) {
            if (!Schema::hasColumn('withdraws', 'wallet_type')) {
                $table->string('wallet_type')->default('main')->after('amount');
            }
        });
    }

    public function down()
    {
        Schema::table('withdraws', function (Blueprint $table) {
            if (Schema::hasColumn('withdraws', 'wallet_type')) {
                $table->dropColumn('wallet_type');
            }
        });
    }
}
