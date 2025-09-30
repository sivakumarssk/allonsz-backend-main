<?php
// Replace the notifications migration with this updated version:
// database/migrations/xxxx_xx_xx_xxxxxx_create_notifications_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('from_id')->nullable();
            $table->string('title');
            $table->text('message');
            $table->enum('type', ['info', 'success', 'warning', 'error'])->default('info');
            $table->boolean('is_read')->default(false);
            $table->boolean('admin_read')->default(false); // Add this column
            $table->string('icon')->nullable();
            $table->string('url')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('from_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['user_id', 'is_read']);
            $table->index(['admin_read']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('notifications');
    }
}