<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKycDocumentSubmissionsTable extends Migration
{
    public function up()
    {
        Schema::create('kyc_document_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->json('file_paths');
            $table->timestamp('submitted_at')->useCurrent();
            $table->string('email_status', 20)->default('pending');
            $table->string('email_message_id')->nullable();
            $table->text('email_error')->nullable();
            $table->unsignedTinyInteger('email_retry_count')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'email_status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('kyc_document_submissions');
    }
}
