<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use \Auth;

class SetupMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::User();
        
        // $name = get aadhar_details name
        
        
        // if($user->is_email_verified != 1){
        //     abort(response()->json(
        //     [
        //         'error' => 'email is not verified',
        //         'redirect' => 'resend_email_otp_screen',
        //     ], 422));
        // }
        // if($user->email_phone_verified != 1){
        //     abort(response()->json(
        //     [
        //         'error' => 'phone is not verified',
        //         'redirect' => 'resend_phone_otp_screen',
        //     ], 422));
        // }
        if($user->password == ''){
            abort(response()->json(
            [
                'error' => 'password is not set',
                'redirect' => 'password_screen',
            ], 422));
        }
        if($user->password == ''){
            abort(response()->json(
            [
                'error' => 'password is not set',
                'redirect' => 'password_screen',
            ], 422));
        }
        if($user->referal_code == ''){
            abort(response()->json(
            [
                'error' => 'profile is not set',
                'redirect' => 'setup_screen',
            ], 422));
        }
        if($user->profile_status == 'Pending'){
            abort(response()->json(
            [
                'error' => 'profile is not set',
                'redirect' => 'setup_screen',
            ], 422));
        }
        
        if($user->aadhar_status == 'Pending'){
            abort(response()->json(
          [
            'error' => 'aadhar is not verified',
                'redirect' => 'aadhar_screen',
         ], 422));
        }

        if($user->pan_status == 'Pending'){
            abort(response()->json(
            [
               'error' => 'pan is not verified',
               'redirect' => 'pan_screen',
           ], 422));
        }
        if($user->bank_status == 'Pending'){
            abort(response()->json(
            [
                'error' => 'bank is not verified',
                'redirect' => 'bank_screen',
            ], 422));
        }
        // if($user->email_sent_document_status == 'Pending'){
        //     abort(response()->json(
        //     [
        //         'error' => 'please send documents to company email',
        //         'redirect' => 'email_document_screen',
        //     ], 422));
        // }
        if($user->status != 'Active'){
            abort(response()->json(
            [
                'error' => 'Accont is not active, please contact us',
                'redirect' => 'inactive_screen',
            ], 422));
        }
        return $next($request);
    }
}
