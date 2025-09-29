<?php 

  

namespace App\Http\Controllers\Auth; 

  

use App\Http\Controllers\Controller;

use Illuminate\Http\Request; 

use DB; 

use Carbon\Carbon; 

use App\Models\User; 

use Mail; 

use Hash;

use Illuminate\Support\Str;

  

class AdminForgotPasswordController extends Controller

{

      public function showForgetPasswordForm()

      {
         return view('admin.forgot_password');
      }
      
      public function submitForgetPasswordForm(Request $request)

      {
            $request->validate([
                'email' => 'required|email|exists:users',
            ]);
            $email = $request->email;
            $user = User::where('email',$email)->where('role','admin')->first();
            $setting = Setting::first();
            if(!$user){
                return back()->with('error', 'The email does not exists in our record');
            }
            
            $token = Str::random(64);
            DB::table('password_resets')->insert([
                'email' => $email,
                'token' => $token, 
                'created_at' => Carbon::now()
            ]);
            
            $info = array(
                'link' => url('reset-password/'.$token),
                'name' => $user->name,
                'logo' => $setting->logo,
                'bussiness_name' => $setting->bussiness_name,
                'email_support' => $setting->email_support,
            );
            try{
                Mail::send('email.forget_password', ["info"=>$info], function ($message) use ($email)
                {
                    $message->to($email)
                    ->subject('Reset Password');
                });
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
            return back()->with('success', 'Reset Password Link sent to your email');
          

      }
      
      public function showResetPasswordForm($token) {
        $password_reset = DB::table('password_resets')->where(['token'=> $token])->first();
        if(!$password_reset){
            return redirect('forget-password')->with('error','Your password reset link has expired');
        }
        return view('admin.reset_password',compact('password_reset'));
      }

      public function submitResetPasswordForm(Request $request)
      {
          $request->validate([
                'email' => 'required|email|exists:users',
                'password' =>[
                'required',
                'string',
                'min:8',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
                'confirmed',
            ],
                'password_confirmation' => 'required',
          ]);
          $updatePassword = DB::table('password_resets')->where(['email' => $request->email, 'token' => $request->token])->first();
          if(!$updatePassword){
              return back()->withInput()->with('error', 'Invalid token!');
          }
          $user = User::where('email', $request->email)->first();
          $user->password = Hash::make($request->password);
          $user->save();
                      
          DB::table('password_resets')->where(['email'=> $request->email])->delete();
          return redirect('/')->with('success', 'Your password has been changed!');
      }

}