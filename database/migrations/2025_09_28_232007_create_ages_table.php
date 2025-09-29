<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAgesTable extends Migration
{
    public function up()
    {
        Schema::create('ages', function (Blueprint $table) {
            $table->id();
            $table->string('range'); // e.g., "18-25", "26-35"
            $table->integer('min_age');
            $table->integer('max_age');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ages');
    }
}

