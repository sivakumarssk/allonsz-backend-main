<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComboCircleRewardsTable extends Migration
{
    public function up()
    {
        Schema::create('combo_circle_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('combo_circle_id');
            $table->decimal('amount', 10, 2);
            $table->string('section')->nullable();
            $table->string('desc')->nullable();
            $table->enum('status', ['Success', 'Failed'])->default('Success');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('combo_circle_id')->references('id')->on('combo_circles')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('combo_circle_rewards');
    }
}
