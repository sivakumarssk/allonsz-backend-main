<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCircleRewardsTable extends Migration
{
    public function up()
    {
        Schema::create('circle_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('circle_id');
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->integer('section')->nullable();
            $table->text('desc')->nullable();
            $table->enum('status', ['Success', 'Pending', 'Failed'])->default('Pending');
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('circle_id')->references('id')->on('circles')->onDelete('cascade');
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('circle_rewards');
    }
}