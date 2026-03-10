<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComboCirclesTable extends Migration
{
    public function up()
    {
        Schema::create('combo_circles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('package_id');
            $table->enum('section', ['five_a', 'five_b', 'twentyone']);
            $table->unsignedInteger('cycle')->default(1);
            $table->unsignedInteger('autofill_count')->default(0);
            $table->enum('status', ['Active', 'Completed', 'Inactive'])->default('Active');
            $table->timestamp('started_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('combo_circles');
    }
}
