<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistrictsTable extends Migration
{
    public function up()
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('state_id');
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('districts');
    }
}
