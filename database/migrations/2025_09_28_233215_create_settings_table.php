<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            // Business info
            $table->string('bussiness_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();

            // SMS & Payment
            $table->string('msg91_key')->nullable();
            $table->string('msg91_sender')->nullable();
            $table->string('msg91_flow_id')->nullable();
            $table->string('razorpay_key')->nullable();
            $table->string('razorpay_secret')->nullable();
            $table->string('fcm_key')->nullable();
            $table->string('google_map_api_key')->nullable();

            // Support info
            $table->string('call_support_number')->nullable();
            $table->string('whatsapp_support_number')->nullable();
            $table->string('email_support')->nullable();

            // Pagination
            $table->integer('pagination')->default(10);

            // Ads
            $table->enum('add_type', ['Image', 'Video'])->nullable();
            $table->string('add_url')->nullable();

            // Taxes and charges
            $table->decimal('cancellation_check_amount', 10, 2)->default(0);
            $table->decimal('cgst', 8, 2)->default(0);
            $table->decimal('sgst', 8, 2)->default(0);
            $table->decimal('tds', 8, 2)->default(0);
            $table->decimal('admin_charge', 8, 2)->default(0);

            // Policies & content
            $table->text('privacy_policy')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('about_us')->nullable();
            $table->text('how_it_works')->nullable();
            $table->text('return_and_refund_policy')->nullable();
            $table->text('accidental_policy')->nullable();
            $table->text('cancellation_policy')->nullable();
            $table->text('faqs')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
