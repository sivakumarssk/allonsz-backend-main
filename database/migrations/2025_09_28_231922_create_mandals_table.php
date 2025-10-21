<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMandalsTable extends Migration
{
    public function up()
    {
        Schema::create('mandals', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('district_id');
            $table->unsignedBigInteger('state_id')->nullable();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mandals');
    }
}
