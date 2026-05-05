<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBlockFreezeToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false)->after('status');
            $table->boolean('wallet_frozen')->default(false)->after('is_blocked');
            $table->string('block_reason')->nullable()->after('wallet_frozen');
            $table->string('freeze_reason')->nullable()->after('block_reason');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_blocked', 'wallet_frozen', 'block_reason', 'freeze_reason']);
        });
    }
}
