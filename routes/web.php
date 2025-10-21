<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SupportController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\AdminForgotPasswordController;

use App\Http\Controllers\Admin\CountryController;
use App\Http\Controllers\Admin\StateController;
use App\Http\Controllers\Admin\DistrictController;
use App\Http\Controllers\Admin\MandalController;
use App\Http\Controllers\Admin\TourController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\RewardController;
use App\Http\Controllers\Admin\TripController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\TransactionController;
Route::get('clear-cache',function(){
    //\Artisan::call('storage:link');
    //\Artisan::call('vendor:publish --provider="Fruitcake\Cors\CorsServiceProvider');
    \Artisan::call('config:clear');
    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    \Artisan::call('route:clear');
    \Artisan::call('config:cache');
});

Route::get('/healthcheck', function () {
    return response('OK', 200);
});

Route::get('/privacypolicy',[AdminController::class, 'privacy_policy']);

Route::middleware('guest')->group(function () {

	Route::get('/',[AdminController::class, 'index'])->name('login');
	Route::post('/login',[AdminController::class, 'login']);
	Route::post('/verifyotp',[AdminController::class, 'verifyotp']);
	
	Route::get('forget-password', [AdminForgotPasswordController::class, 'showForgetPasswordForm']);
	Route::post('forget-password', [AdminForgotPasswordController::class, 'submitForgetPasswordForm']);
    Route::get('reset-password/{token}', [AdminForgotPasswordController::class, 'showResetPasswordForm']);
    Route::post('reset-password', [AdminForgotPasswordController::class, 'submitResetPasswordForm']);
});

Route::post('/save-token',[AdminController::class, 'save_token'])->name('save.token');

Route::middleware('admin')->group(function () {
    
    Route::post('/login-as-vendor',[AdminController::class, 'login_as_vendor']);
    Route::post('/post-clear-database',[AdminController::class, 'post_clear_database']);
	Route::get('/dashboard',[AdminController::class, 'dashboard']);
    Route::get('/clear-database',[AdminController::class, 'clear_database']);
	Route::get('/profile',[AdminController::class, 'profile']);
	Route::post('/update-profile',[AdminController::class, 'update_profile']);
	Route::post('/change-password',[AdminController::class, 'change_password']);
	Route::get('/customers',[AdminController::class, 'customers']);
	Route::get('/show-customer/{id}',[AdminController::class, 'show_customer']);
	Route::post('/store-user',[AdminController::class, 'store_user']);
	Route::post('/delete-user',[AdminController::class, 'delete_user']);
	Route::post('/update-user-status',[AdminController::class, 'update_user_status']);
	Route::post('/update-document-status',[AdminController::class, 'update_document_status']);
    Route::get('/customer-timers',[AdminController::class, 'customer_timers']);
    Route::get('/expiring-timers',[AdminController::class, 'users_with_expiring_timers']);
	Route::get('/withdraws',[AdminController::class, 'withdraws']);
	Route::post('/update-withdraw',[AdminController::class, 'update_withdraw']);
    Route::get('clear-purchase-history/{id}', [AdminController::class,'clear_purchase_history']);
	Route::resource('/trip', TripController::class);

    Route::resource('/order', OrderController::class);
    Route::resource('/subscription', SubscriptionController::class);
    Route::resource('/transaction', TransactionController::class);
	
	Route::resource('/country', CountryController::class);
	Route::resource('/state', StateController::class);
	Route::resource('/district', DistrictController::class);
	Route::resource('/mandal', MandalController::class);
	Route::resource('/tour', TourController::class);
	Route::resource('/package', PackageController::class);
	Route::post('/update-color', [PackageController::class, 'update_color']);
	Route::resource('/reward',RewardController::class);

    Route::post('/get-countries', [AdminController::class,'get_countries']);
    Route::post('/get-states', [AdminController::class,'get_states']);
    Route::post('/get-districts', [AdminController::class,'get_districts']);
    Route::post('/get-mandals', [AdminController::class,'get_mandals']);

	Route::resource('/notification', NotificationController::class);
    Route::resource('/setting',SettingController::class);
    Route::get('/add',[SettingController::class,'add']);
    Route::post('/update-add',[SettingController::class,'update_add']);
    Route::get('/taxes',[SettingController::class, 'taxes']);
    Route::post('/update-taxes',[SettingController::class, 'update_taxes']);
    Route::get('/privacy-policy',[SettingController::class, 'privacy_policy']);
    Route::post('/update-privacy-policy',[SettingController::class, 'update_privacy_policy']);
    Route::get('/terms-conditions',[SettingController::class, 'terms_conditions']);
    Route::post('/update-terms-conditions',[SettingController::class, 'update_terms_conditions']);
    Route::get('/about-us',[SettingController::class, 'about_us']);
    Route::post('/update-about-us',[SettingController::class, 'update_about_us']);
    Route::get('/how-it-works',[SettingController::class, 'how_it_works']);
    Route::post('/update-how-it-works',[SettingController::class, 'update_how_it_works']);
    Route::get('/return-and-refund-policy',[SettingController::class, 'return_and_refund_policy']);
    Route::post('/update-return-and-refund-policy',[SettingController::class, 'update_return_and_refund_policy']);
    Route::get('/accidental-policy',[SettingController::class, 'accidental_policy']);
    Route::post('/update-accidental-policy',[SettingController::class, 'update_accidental_policy']);
    Route::get('/cancellation-policy',[SettingController::class, 'cancellation_policy']);
    Route::post('/update-cancellation-policy',[SettingController::class, 'update_cancellation_policy']);
    Route::get('/faqs',[SettingController::class, 'faqs']);
    Route::post('/update-faqs',[SettingController::class, 'update_faqs']);

	Route::post('/logout',[AdminController::class, 'logout']);
});