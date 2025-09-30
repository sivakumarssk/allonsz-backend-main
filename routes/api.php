<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\TruthScreenController;

// Route::prefix('customer')->group(function () {
    Route::post('login',[CustomerController::class,'login']);
    Route::post('send-otp',[CustomerController::class,'send_otp']);
    Route::post('resend-otp',[CustomerController::class,'resend_otp']);
    Route::post('forget-password',[CustomerController::class,'forget_password']);
    Route::post('verify-otp',[CustomerController::class,'verify_otp']);

    Route::post('get-setting',[CustomerController::class,'get_setting']);
    Route::post('get-logo',[CustomerController::class,'get_logo']);
    Route::post('onboarding',[CustomerController::class,'onboarding']);
    
    Route::middleware(['auth:api'])->group(function (){
        Route::post('setup-screen',[CustomerController::class,'setup_screen']);
        Route::post('setup-otp',[CustomerController::class,'setup_otp']);
        Route::post('update-password',[CustomerController::class,'update_password']);
        Route::post('update-profile',[CustomerController::class,'update_profile']);
        Route::post('update-profile-photo',[CustomerController::class,'update_profile_photo']);
        Route::post('delete-account',[CustomerController::class,'delete_account']);
        Route::post('get-countries',[CustomerController::class,'get_countries']);
        Route::post('get-states',[CustomerController::class,'get_states']);
        Route::post('get-districts',[CustomerController::class,'get_districts']);
        Route::post('get-mandals',[CustomerController::class,'get_mandals']);
        Route::post('get-aadhar-otp', [TruthScreenController::class, 'get_aadhar_otp']);
        Route::post('verify-aadhar-otp', [TruthScreenController::class, 'verify_aadhar_otp']);
        Route::post('verify-pan-number', [TruthScreenController::class, 'verify_pan_number']);
        Route::post('update-bank-details', [TruthScreenController::class, 'update_bank_details']);
        
        Route::post('get-aadhar-validation-link', [TruthScreenController::class, 'get_aadhar_validation_link']);
        Route::post('validate-aadhar', [TruthScreenController::class, 'validate_aadhar']);
        
        Route::middleware('setup')->group(function (){
            Route::get('user-status',[CustomerController::class,'user_status']);
            Route::post('profile',[CustomerController::class,'profile']);
            
            Route::post('get-tours',[CustomerController::class,'get_tours']);
            Route::post('tour-details',[CustomerController::class,'tour_details']);
            Route::post('get-circles',[CustomerController::class,'get_circles']);
            Route::post('get-completed-circles',[CustomerController::class,'get_completed_circles']);
            Route::post('get-downline-circle',[CustomerController::class,'get_downline_circle']);
            Route::post('get-packages',[CustomerController::class,'get_packages']);
            Route::post('my-trips',[CustomerController::class,'my_trips']);
            Route::post('request-trip',[CustomerController::class,'request_trip']);
            Route::post('update-trip',[CustomerController::class,'update_trip']);
            Route::post('upload-trip-photo',[CustomerController::class,'upload_trip_photo']);
            Route::post('withdraw-request',[CustomerController::class,'withdraw_request']);
            Route::get('withdraw-history',[CustomerController::class,'withdraw_history']);
            Route::get('transaction-history',[CustomerController::class,'transaction_history']);
            Route::get('get-add',[CustomerController::class,'get_add']);
            Route::get('get-timer',[CustomerController::class,'get_timer']);
             
            Route::post('update-referal-code',[CustomerController::class,'update_referal_code']);
            
            Route::post('create-razorpay-order', [CustomerController::class,'create_razorpay_order']);
            Route::post('verify-razorpay-signature', [CustomerController::class,'verify_razorpay_signature']);
            Route::post('create-circle-manually', [CustomerController::class,'create_circle_manually']);
            
            Route::post('get-notifications', [CustomerController::class,'get_notifications']);
            Route::post('read-notification', [CustomerController::class,'read_notification']);
            
            Route::post('create-circle',[CustomerController::class,'create_circle']);
            
        });
        
    });
    
    
// });




