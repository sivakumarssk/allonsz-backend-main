<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('package_id');
            $table->string('order_id')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('order_number')->nullable()->unique();
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('signature')->nullable();
            $table->date('start_date')->nullable();
            $table->enum('status', ['pending', 'Created', 'Verified', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('packages')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
