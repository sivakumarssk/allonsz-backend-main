<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComboMembersTable extends Migration
{
    public function up()
    {
        Schema::create('combo_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('combo_circle_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedInteger('position');
            $table->enum('status', ['Empty', 'Occupied'])->default('Empty');
            $table->enum('placement_type', ['direct', 'autofill'])->nullable();
            $table->unsignedBigInteger('package_id');
            $table->timestamps();

            $table->foreign('combo_circle_id')->references('id')->on('combo_circles')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('combo_members');
    }
}
