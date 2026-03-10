<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddComboWalletToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'combo_wallet')) {
                $table->decimal('combo_wallet', 10, 2)->default(0)->after('not_withdraw_amount');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'combo_wallet')) {
                $table->dropColumn('combo_wallet');
            }
        });
    }
}
