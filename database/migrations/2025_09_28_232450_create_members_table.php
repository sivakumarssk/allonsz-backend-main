<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembersTable extends Migration
{
    public function up()
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('circle_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('package_id');
            $table->integer('position');
            $table->enum('status', ['Empty', 'Occupied'])->default('Empty');
            $table->timestamps();
            
            $table->foreign('circle_id')->references('id')->on('circles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('members');
    }
}
