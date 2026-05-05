<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferralHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('referral_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('old_referal_id')->nullable();
            $table->string('old_referal_code')->nullable();
            $table->unsignedBigInteger('new_referal_id')->nullable();
            $table->string('new_referal_code')->nullable();
            $table->unsignedBigInteger('changed_by')->nullable(); // admin who made the change
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('referral_histories');
    }
}
