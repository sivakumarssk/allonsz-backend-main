<?php
// 1. Update users table migration
// database/migrations/xxxx_xx_xx_xxxxxx_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('role')->default('user');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->unique()->nullable();
            $table->string('email')->unique();
            $table->string('email_status')->default('Unverified');
            $table->string('phone')->unique()->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('pincode')->nullable();
            $table->string('photo')->nullable();
            $table->decimal('wallet', 10, 2)->default(0);
            $table->string('referal_code')->unique()->nullable();
            $table->unsignedBigInteger('referal_id')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->unsignedBigInteger('mandal_id')->nullable();
            $table->unsignedBigInteger('age_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('aadhar_no')->nullable()->unique();
            $table->string('pan_no')->nullable()->unique();
            $table->string('otp')->nullable();
            $table->string('otp_status')->nullable();
            $table->longText('api_token')->nullable();
            $table->string('device_token')->nullable();
             $table->enum('profile_status', ['Pending', 'Verified'])->default('Pending');
            $table->enum('aadhar_status', ['Pending', 'Verified'])->default('Pending');
            $table->enum('pan_status', ['Pending', 'Verified'])->default('Pending');
            $table->enum('bank_status', ['Pending', 'Verified'])->default('Pending');
            $table->enum('document_status', ['Pending', 'Verified'])->default('Pending');
            $table->enum('status', ['Active','Inactive','Pending'])->default('pending');
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $table->foreign('mandal_id')->references('id')->on('mandals')->onDelete('set null');
            $table->foreign('referal_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
}