<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use App\Models\Country;
use App\Models\State;
use App\Models\District;
use App\Models\Mandal;
use App\Models\Tour;
use App\Models\Package;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\Transaction;
use App\Models\Photo;
use App\Models\Circle;
use App\Models\CircleReward;
use App\Models\Member;
use App\Models\ComboCircle;
use App\Models\ComboMember;
use App\Models\ComboCircleReward;
use App\Models\Timer;
use App\Models\Trip;
use App\Models\Withdraw;
use App\Models\Color;
use DB;
use \Session;
use Mail;
use \Str;
use Illuminate\Support\Facades\Validator;
use Razorpay\Api\Api;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;


use \Hash;
use \Auth;

class CustomerController extends Controller
{
    public function __construct()
    {
        
        $rdata = Setting::findOrFail(1);
        $this->keyId = $rdata->razorpay_key;
        $this->keySecret = $rdata->razorpay_secret;
        $this->displayCurrency = 'INR';

        $this->api = new Api($this->keyId, $this->keySecret);
    }
    
    public function get_setting()
    {
        $setting = Setting::first();
        return response()->json([
            'setting' => $setting
        ],200); 
    }
    
    public function user_status()
    {
        $user = Auth::User();
        
        return response()->json([
            'profile_status' => $user->profile_status,
            'document_status' => $user->document_status,
            'aadhar_status' => $user->aadhar_status,
            'pan_status' => $user->pan_status,
            'bank_status' => $user->bank_status,
            'status' => $user->status,
        ],200); 
    }
    
    public function send_otp(Request $request)
    {
        $rules = [
            'email' => 'nullable|email|required_without:phone',
            'phone' => 'nullable|required_without:email',
        ];
        
        $validation = \Validator::make( $request->all(), $rules );
        $error = $validation->errors()->first();
        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors()->first()
            ], 422);
        }
        if($request->email){
            $user = User::where('email',$request->email)->first();
        }else{
            $user = User::where('phone',$request->phone)->first();
        }
        if($user){
            if($user->status == 'Active'){
                return response()->json([
                    'error' => 'You are already registerd with us, Please login'
                ],422);
            }
            
        }else{
            $user = new User();
        }
        
        $user->role = 'customer';
        $otp = rand(1000,9999);
        if($request->email){
            $user->email = $request->email;
        }else{
            $user->phone = $request->phone;
        }
        
        $user->status = 'Pending';
        $user->otp = Hash::make($otp);
        $user->otp_status = 'Sent';
        $user->referal_code = 'AAZ'.rand(100000000,999999999);
        $user->save();
        
        $setting = Setting::first();
        $logo = $setting->logo;
        if($request->email){
            $info = array(
                'name' => $user->name,
                'otp' => $otp,
                'logo' => $logo,
                'bussiness_name' => $setting->bussiness_name,
                'email_support' => $setting->email_support,
            );
            try{
                Mail::send('email.forget_password2', $info, function ($message) use ($user)
                {
                    $message->to($user->email, $user->name)
                    ->subject('Allons-Z ONE TIME PASSWORD');
                });
            } catch (\Exception $e) {
                $error = $e->getMessage();
                    return response()->json([
                    'error' => $error
                ],422);
            }
        }else{
            $name = $user->name;
                $msg = "Dear {$name}, your allons-z verification code is {$otp}";
                $url = "https://smslogin.co/v3/api.php";
                $response = Http::get($url, [
                    'username'   => 'ALLONZ',
                    'apikey'     => '078db3e07ba667b4ec3d',
                    'senderid'   => 'ALLONZ',
                    'mobile'     => $user->phone,  // replace 'xxxxxxxxx' with $user->phone
                    'message'    => $msg,
                    'templateid' => '1407174167988449830',
                ]);
        }
            
        return response()->json([
            'msg' => 'otp sent successfully.'
        ],200);
    }
    
    public function resend_otp(Request $request)
    {
        $rules = [
            'email' => 'nullable|email|required_without:phone',
            'phone' => 'nullable|required_without:email',
        ];
        $validation = \Validator::make( $request->all(), $rules );
        $error = $validation->errors()->first();
        if($error){
            return response()->json([
                'error' => $error
            ],422);
        }
        
        if($request->email){
            $user = User::where('email',$request->email)->first();
        }else{
            $user = User::where('phone',$request->phone)->first();
        }
        if(!$user){
            return response()->json([
                'error' => 'This email is not registered with us'
            ],422);
        }
        
        $otp = rand(1000,9999);
        $setting = Setting::first();
        $logo = $setting->logo;
        $info = array(
            'name' => $user->name,
            'otp' => $otp,
            'logo' => $logo,
            'bussiness_name' => $setting->bussiness_name,
            'email_support' => $setting->email_support,
        );
        if($request->email){
            try{
                Mail::send('email.forget_password2', $info, function ($message) use ($user)
                {
                    $message->to($user->email, $user->name)
                    ->subject('Allons-Z ONE TIME PASSWORD');
                });
            } catch (\Exception $e) {
                $error = $e->getMessage();
                return response()->json([
                    'error' => $error
                ],422);
            }
        }else{
            $name = $user->name;
                $msg = "Dear {$name}, your allons-z verification code is {$otp}";
                $url = "https://smslogin.co/v3/api.php";
                $response = Http::get($url, [
                    'username'   => 'ALLONZ',
                    'apikey'     => '078db3e07ba667b4ec3d',
                    'senderid'   => 'ALLONZ',
                    'mobile'     => $user->phone,  // replace 'xxxxxxxxx' with $user->phone
                    'message'    => $msg,
                    'templateid' => '1407174167988449830',
                ]);
        }

        $user->otp = Hash::make($otp);
        $user->otp_status = 'Sent';
        $user->save();

        return response()->json([
            'msg' => 'otp sent successfully.'
        ],200);
    }

    public function setup_screen(Request $request)
    {
        $user = Auth::User();
        return response()->json([
            'email' => $user->email,
            'phone' => $user->phone
        ],200);
    }

    public function setup_otp(Request $request)
    {
        $rules = [
            'email' => 'nullable|email|required_without:phone',
            'phone' => 'nullable|required_without:email',
        ];
        $validation = \Validator::make( $request->all(), $rules );
        $error = $validation->errors()->first();
        if($error){
            return response()->json([
                'error' => $error
            ],422);
        }
        if($request->phone){
            $existing_user = User::where('phone',$request->phone)->first();
            if($existing_user){
                return response()->json([
                    'error' => 'This phone is already taken'
                ],422);
            }
        }
        $user = Auth::User();
        $otp = rand(1000,9999);
        $setting = Setting::first();
        $logo = $setting->logo;
        $info = array(
            'name' => $user->name,
            'otp' => $otp,
            'logo' => $logo,
            'bussiness_name' => $setting->bussiness_name,
            'email_support' => $setting->email_support,
        );
        if($request->email){
            try{
                Mail::send('email.forget_password2', $info, function ($message) use ($user)
                {
                    $message->to($user->email, $user->name)
                    ->subject('Allons-Z ONE TIME PASSWORD');
                });
            } catch (\Exception $e) {
                $error = $e->getMessage();
                return response()->json([
                    'error' => $error
                ],422);
            }
        }else{
            $name = $user->name;
                $msg = "Dear {$name}, your allons-z verification code is {$otp}";
                $url = "https://smslogin.co/v3/api.php";
                $response = Http::get($url, [
                    'username'   => 'ALLONZ',
                    'apikey'     => '078db3e07ba667b4ec3d',
                    'senderid'   => 'ALLONZ',
                    'mobile'     => $user->phone,  // replace 'xxxxxxxxx' with $user->phone
                    'message'    => $msg,
                    'templateid' => '1407174167988449830',
                ]);
        }

        $user->otp = Hash::make($otp);
        $user->otp_status = 'Sent';
        $user->save();

        return response()->json([
            'msg' => 'otp sent successfully.'
        ],200);
    }
    
    public function forget_password(Request $request)
    {
        $rules = [
            'email' => 'nullable|email|required_without:phone',
            'phone' => 'nullable|required_without:email',
        ];
        $validation = \Validator::make( $request->all(), $rules );
        $error = $validation->errors()->first();
        if($error){
            return response()->json([
                'error' => $error
            ],422);
        }
        
        if($request->email){
            $user = User::where('email',$request->email)->first();
        }else{
            $user = User::where('phone',$request->phone)->first();
        }
        if(!$user){
            return response()->json([
                'error' => 'This email or phone is not registered with us'
            ],422);
        }
        
        $otp = rand(1000,9999);
        $setting = Setting::first();
        $logo = $setting->logo;
        $info = array(
            'name' => $user->name,
            'otp' => $otp,
            'logo' => $logo,
            'bussiness_name' => $setting->bussiness_name,
            'email_support' => $setting->email_support,
        );
        if($request->email){
            try{
                Mail::send('email.forget_password2', $info, function ($message) use ($user)
                {
                    $message->to($user->email, $user->name)
                    ->subject('Allons-Z Forget Password');
                });
            } catch (\Exception $e) {
                $error = $e->getMessage();
                return response()->json([
                    'error' => $error
                ],422);
            }
        }else{
            $name = $user->name;
                $msg = "Dear {$name}, your allons-z verification code is {$otp}";
                $url = "https://smslogin.co/v3/api.php";
                $response = Http::get($url, [
                    'username'   => 'ALLONZ',
                    'apikey'     => '078db3e07ba667b4ec3d',
                    'senderid'   => 'ALLONZ',
                    'mobile'     => $user->phone,  // replace 'xxxxxxxxx' with $user->phone
                    'message'    => $msg,
                    'templateid' => '1407174167988449830',
                ]);
        }
        

        $user->otp = Hash::make($otp);
        $user->otp_status = 'Sent';
        $user->save();

        return response()->json([
            'msg' => 'otp sent successfully.'
        ],200);
    }
    
    public function verify_otp(Request $request)
    {
        $rules = [
            'email' => 'nullable|email|required_without:phone',
            'phone' => 'nullable|required_without:email',
            'otp' => 'required|numeric|digits:4',
        ];
        $validation = \Validator::make( $request->all(), $rules );
        $error = $validation->errors()->first();
        if($error){
            return response()->json([
                'error' => $error
            ],422);
        }
        if($request->email){
            $user = User::where('email',$request->email)->first();
        }else{
            $user = User::where('phone',$request->phone)->first();
        }
        if($user){
            if (Hash::check($request->otp, $user->otp)) {
                $user->otp_status = 'Verified';
                $user->email_status = 'Verified';
                $token = $user->createToken('MyApp')->accessToken;
                $user->api_token = $token;
                $user->device_token = $request->device_token;
                $user->save();
                Auth::login($user, true);
                return response()->json([
                    'token' => $token,
                    'msg' => 'OTP verified successfully.'
                ],200);
            }
        }
        return response()->json([
                'error' => 'Invalid email or OTP.'
        ],422);
    }
    
    public function update_password(Request $request)
    {
        $rules = [
            'password' => [
                'required',
                'string',
                'min:8',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
                'confirmed',
            ],
        ];

        $validation = \Validator::make( $request->all(), $rules );
        $error = $validation->errors()->first();
        if($error){
            return response()->json([
                'error' => $error
            ],422);
        }
        
        $user = Auth::User();
        
        if($user){
            $user->password = Hash::make($request->password);
            $user->status = 'Active';
            $user->save();
            return response()->json([
                'msg' => 'Password updated.'
            ],200);
        }
        return response()->json([
                'error' => 'User not found.'
        ],422);
    }

    public function login(Request $request)
    {
        $rules = [
            
            'email' => 'nullable|email|required_without:phone',
            'phone' => 'nullable|required_without:email',
            'password' => [
                'required',
            ],
        ];

        $validation = \Validator::make( $request->all(), $rules );
        $error = $validation->errors()->first();
        if($error){
            return response()->json([
                'error' => $error
            ],422);
        }
        
        if($request->email){
            $user = User::where('email',$request->email)->first();
        }else{
            $user = User::where('phone',$request->phone)->first();
        }
        if($user){
            if (Hash::check($request->password, $user->password) || $request->type == 'manual') {
                $token = $user->createToken('MyApp')->accessToken;
                $user->api_token = $token;
                $user->device_token = $request->device_token;
                $user->save();
                Auth::login($user, true);
                return response()->json([
                    'token' => $token
                ],200);
            }else{
                return response()->json([
                    'error' => 'Invalid Password !!'
                ],422);
            }
        }
        return response()->json([
            'error' => 'This email is not registered with us!'
        ],422);
    }

    public function profile(Request $request)
    {
        $user = Auth::User();
        $user = User::where('id',$user->id)->with(['country','state','district','mandal'])->first();
        $user->referal_id = $user->referal ? $user->referal->referal_code : 'N/A';
        
        // Add wallet breakdown for 5-member circle rewards
        $wallet = $user->wallet ?? 0;
        $not_withdraw_amount = $user->not_withdraw_amount ?? 0;
        $combo_wallet = $user->combo_wallet ?? 0;
        // Total amount includes combo wallet, but combo wallet is not withdrawable
        $total_wallet = $wallet + $not_withdraw_amount + $combo_wallet;

        // Combo wallet breakdown: locked amount for auto-renewal
        $combo_locked_for_autorenew = $this->combo_get_locked_autorenew_amount($user->id);
        $combo_pending_withdrawals = Withdraw::where('user_id', $user->id)
            ->where('wallet_type', 'combo')
            ->where('status', 'Pending')
            ->sum('amount');
        $combo_withdrawable = max(0, $combo_wallet - $combo_locked_for_autorenew - $combo_pending_withdrawals);

        return response()->json([
            'user' => $user,
            'wallet_breakdown' => [
                'withdrawable_amount' => $wallet,
                'non_withdrawable_amount' => $not_withdraw_amount,
                'total_amount' => $total_wallet,
                'combo_wallet' => $combo_wallet,
                'combo_locked_for_autorenew' => $combo_locked_for_autorenew,
                'combo_pending_withdrawals' => $combo_pending_withdrawals,
                'combo_withdrawable' => $combo_withdrawable
            ]
        ],200);
    }
    
    public function update_profile(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'first_name' => 'required|string|max:255|regex:/^[A-Z][a-z]*$/',
            'last_name' => 'required|string|max:255|regex:/^[A-Z][a-z]*$/',
            'email' => 'nullable|email',
            'phone' => 'required|unique:users,phone,' . $user->id . '|regex:/^([0-9\s\-\+\(\)]*)$/|size:10',
            'username' => 'required|unique:users,phone,' . $user->id . '|min:3',
            'gender' => 'required|in:Male,Female,Other',
            'country_id' => 'required|numeric',
            'state_id' => 'required|numeric',
            'district_id' => 'required|numeric',
            'mandal_id' => 'required|numeric',
            'address' => 'required|string|min:4',
            'pincode' => 'required|numeric|digits:6|',
            'referal_code' => 'required|string|min:8|exists:users,referal_code',
        ];
        
        $messages = [
            'referal_code.exists' => 'The unique id you entered is invalid. Please check and try again.',
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        //return $request->all();
        if($request->hasFile('photo')) {
            $file = $request->file('photo');
            
            // Validate that we have a valid file object
            if (!$file || !$file->isValid()) {
                return response()->json([
                    'error' => 'Invalid file uploaded'
                ], 422);
            }
            
            // Get extension from original filename
            $extension = strtolower($file->getClientOriginalExtension());
            
            // If extension is missing or invalid, determine it from MIME type
            if (empty($extension) || !in_array($extension, ['jpeg', 'jpg', 'png'])) {
                $mimeType = $file->getMimeType();
                $extensionMap = [
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                ];
                
                if (isset($extensionMap[$mimeType])) {
                    $extension = $extensionMap[$mimeType];
                } else {
                    return response()->json([
                        'error' => 'Invalid file format. Please upload a JPEG or PNG image.'
                    ], 422);
                }
            }
            
            // Normalize extension to lowercase
            $extension = strtolower($extension);
            
            // Validate extension
            $allowedfileExtension = ['jpeg', 'jpg', 'png'];
            $check = in_array($extension, $allowedfileExtension);
            
            if($check){
                $oldFilePath = public_path('/images/profiles/'.$user->photo_filename);
                if (file_exists($oldFilePath) && $user->photo_filename != '') {
                    unlink($oldFilePath);
                }
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $filename = substr(str_shuffle(str_repeat($pool, 5)), 0, 12) .'.'.$extension;
                
                try {
                    $path = $file->move(public_path('/images/profiles'), $filename);
                    $user->photo = $filename;
                } catch (\Exception $e) {
                    return response()->json([
                        'error' => 'Failed to upload file: ' . $e->getMessage()
                    ], 422);
                }
            }else{
                return response()->json([
                    'error' => 'Invalid file format, please upload valid image file (JPEG or PNG)'
                ], 422);
            }
        }
        $referal = User::where('referal_code',$request->referal_code)->first();

        // Check if upline has any active package (regular or combo)
        $member = Member::where('user_id',$referal->id)->first();
        $combo_circle = ComboCircle::where('user_id', $referal->id)->first();

        if(!$member && !$combo_circle){
            return response()->json([
                'error' => 'Upliner has not any active package',
            ], 422);
        }
        
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        if($request->email){
            $user->email = $request->email;
        }
        if($request->phone){
            $user->phone = $request->phone;
        }
        $user->username = $request->username;
        $user->gender = $request->gender;
        $user->country_id = $request->country_id;
        $user->state_id = $request->state_id;
        $user->district_id = $request->district_id;
        $user->mandal_id = $request->mandal_id;
        $user->address = $request->address;
        $user->pincode = $request->pincode;
        $user->referal_id = $referal->id;
        $user->profile_status = 'Verified';
        $user->save();
    
        return response()->json([
            'msg' => 'Profile updated',
            'user' => $user
        ], 200);
    }
    
    public function update_profile_photo(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'photo' => 'required|image|mimes:jpeg,jpg,png|max:5120',
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        
        if($request->hasFile('photo')) {
            $file = $request->file('photo');
            
            // Validate that we have a valid file object
            if (!$file || !$file->isValid()) {
                return response()->json([
                    'error' => 'Invalid file uploaded'
                ], 422);
            }
            
            // Get extension from original filename
            $extension = strtolower($file->getClientOriginalExtension());
            
            // If extension is missing or invalid, determine it from MIME type
            if (empty($extension) || !in_array($extension, ['jpeg', 'jpg', 'png'])) {
                $mimeType = $file->getMimeType();
                $extensionMap = [
                    'image/jpeg' => 'jpg',
                    'image/jpg' => 'jpg',
                    'image/png' => 'png',
                ];
                
                if (isset($extensionMap[$mimeType])) {
                    $extension = $extensionMap[$mimeType];
                } else {
                    return response()->json([
                        'error' => 'Invalid file format. Please upload a JPEG or PNG image.'
                    ], 422);
                }
            }
            
            // Normalize extension to lowercase
            $extension = strtolower($extension);
            
            // Validate extension
            $allowedfileExtension = ['jpeg', 'jpg', 'png'];
            if (!in_array($extension, $allowedfileExtension)) {
                return response()->json([
                    'error' => 'Invalid file format, please upload valid image file (JPEG or PNG)'
                ], 422);
            }
            
            // Delete old photo if exists
            $oldFilePath = public_path('/images/profiles/'.$user->photo_filename);
            if (file_exists($oldFilePath) && $user->photo_filename != '') {
                unlink($oldFilePath);
            }
            
            // Generate unique filename
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $filename = substr(str_shuffle(str_repeat($pool, 5)), 0, 12) .'.'.$extension;
            
            // Move file to destination
            try {
                $path = $file->move(public_path('/images/profiles'), $filename);
                $user->photo = $filename;
            } catch (\Exception $e) {
                return response()->json([
                    'error' => 'Failed to upload file: ' . $e->getMessage()
                ], 422);
            }
        }
        
        $user->save();
    
        return response()->json([
            'msg' => 'Profile photo updated successfully',
            'user' => $user
        ], 200);
    }

    public function delete_account()
    {
        $user = Auth::User();
        $user->delete();
        return response()->json([
            'msg' => 'Account deleted successfully'
        ], 200);
    }
    
    public function get_countries(Request $request)
    {
        $countries = Country::all();
        return response()->json([
            'countries' => $countries
        ], 200);
    }
    public function get_states(Request $request)
    {
        $rules = [
            'country_id' => 'required|numeric',
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        $states = State::where('country_id',$request->country_id)->get();
        return response()->json([
            'states' => $states
        ], 200);
    }
    public function get_districts(Request $request)
    {
        $rules = [
            'state_id' => 'required|numeric',
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        $districts = District::where('state_id',$request->state_id)->get();
        return response()->json([
            'districts' => $districts
        ], 200);
    }
    public function get_mandals(Request $request)
    {
        $rules = [
            'district_id' => 'required|numeric',
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        $mandals = Mandal::where('district_id',$request->district_id)->get();
        return response()->json([
            'mandals' => $mandals
        ], 200);
    }
    
    public function get_tours(Request $request)
    {
        $rules = [
            'type' => 'required',
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        $query = Tour::query();
        $query = $query->where('type',$request->type);
        if($request->keyword){
            $query->where('name', 'LIKE', "%" . $request->keyword . "%")
            ->orWhere('place', 'LIKE', "%" . $request->keyword . "%")
            ->orWhere('area', 'LIKE', "%" . $request->keyword . "%")
            ->orWhere('price', 'LIKE', "%" . $request->keyword . "%");
        }
        $tours = $query->get();
        return response()->json([
                'tours' => $tours
        ],200);
    }
    
    public function tour_details(Request $request)
    {
        $rules = [
            'tour_id' => 'required',
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        $tour = Tour::where('id',$request->tour_id)->with('photos')->get();
        return response()->json([
                'tour' => $tour
        ],200);
    }
    
    public function update_referal_code(Request $request)
    {
        $user = Auth::user();
        $rules = [
            'referal_code' => 'required|unique:users,referal_code,' . $user->id . '|string|size:8',
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        $user->referal_code = $request->referal_code;
        $user->save();
        return response()->json([
                'msg' => 'Referal code updated'
        ],200);
    }
    
   public function get_packages(Request $request)
{
    $setting = Setting::first();
    $user = Auth::User();
    $packages = collect();
    
    // Admin (user_id = 1) should see all packages without subscription
    // Admin is the first person in all packages (regular and combo)
    if($user->id == 1){
        $packages = Package::all();
    } else {
        $subscription = Member::where('user_id',$user->id)->first();
        if($subscription){
            // If user has subscription, get all packages (including combo)
            $packages = Package::all();
        } else {
            // Check packages from upline
            $upline = $user->referal;
            $packageIds = collect();
            
            if($upline){
                // Get regular packages from upline's circles
                $memberIds = Member::where('user_id', $upline->id)
                    ->selectRaw('MAX(id) as id')
                    ->groupBy('package_id')
                    ->pluck('id');

                $circleds = Member::whereIn('id', $memberIds)
                    ->where('user_id',$upline->id)
                        ->orderBy('id', 'desc')
                        ->pluck('circle_id');

                $circles = Circle::whereIn('id',$circleds)->with(['package','members'])->get();
                $regularPackageIds = $circles->pluck('package_id')->unique()->values();
                
                // Get combo packages from upline's combo circles
                $comboCircles = ComboCircle::where('user_id', $upline->id)
                    ->where('status', 'Active')
                    ->get();
                
                $comboPackageIds = $comboCircles->pluck('package_id')->unique()->values();
                
                // Merge regular package IDs with combo package IDs
                $packageIds = $regularPackageIds->merge($comboPackageIds)->unique()->values();
            }

            if($packageIds->isNotEmpty()){
                $packages = Package::whereIn('id',$packageIds)->get();
            }
        }
    }
    
    foreach($packages as $package){
        $sgst = (($setting->sgst * $package->price) / 100);
        $cgst = (($setting->cgst * $package->price) / 100);
            $total =  $sgst + $cgst + $package->price;
            $package['sgst'] = $sgst;
            $package['cgst'] = $cgst;
            $package['total'] = $total;
    }
    return response()->json([
        'packages' => $packages
        ],200);
}

    public function get_circles(Request $request)
    {
        $user = Auth::User();
        // active_circles() already includes 5-member circles where user is the owner
        $all_circles = $user->active_circles();
        
        foreach($all_circles as $circle){
            // Check if this is a 5-member circle
            $is_5_member = $circle->package->total_members == 5;
            
            if($is_5_member){
                // For 5-member circles, add completion message if completed
                if($circle->status == 'Completed'){
                    $circle['completion_message'] = 'Circle completed. Please update the package.';
                }
            } else {
                // Existing logic for 2, 3, 4 downline circles
                $timer = Timer::where('user_id', $user->id)->where('package_id', $circle->package_id)->first();
                if($timer){
                    $purchased_at = $timer->started_at;
                    $expiresAt = $purchased_at->copy()->addDays(120); // Changed from 60 to 120 days (4 months)
                    $circle['purchased_at'] = $expiresAt;
                }
            }
            
            foreach($circle->members as $member){
                $color = Color::where('package_id', $circle->package_id)->where('position', $member->position)->first();
                if($color){
                    $member['color'] = $color->color;
                }
                $member['is_downline'] = false;
                if($member->user)
                {
                    if($member->user->referal_id == $user->id || $member->user_id == $user->id){
                        $member['is_downline'] = true;
                    }
                }
            }
        }
        
        // Combo circles - similar logic to active_circles() for 21-member circles
        // Get combo circles where user is a member (for 21-member circles)
        // AND combo circles where user is the owner (for 5-member circles)
        
        // For 21-member combo circles: show circles where user is a member (until position 21)
        // Similar to active_circles() for regular 21-member circles
        $comboMemberIds = ComboMember::where('user_id', $user->id)
            ->whereHas('circle', function($query) {
                $query->where('section', 'twentyone')
                      ->where('status', 'Active');
            })
            ->selectRaw('MAX(id) as id')
            ->groupBy('package_id')
            ->pluck('id');

        $comboCircleIds = ComboMember::whereIn('id', $comboMemberIds)
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->pluck('combo_circle_id')
            ->unique()
            ->values();

        $combo_21_circles = ComboCircle::whereIn('id', $comboCircleIds)
            ->where('section', 'twentyone')
            ->where('status', 'Active')
            ->with(['package', 'members'])
            ->get();

        // For 5-member combo circles: ONLY include circles where user is the owner (center position)
        $combo_5_circles = ComboCircle::where('user_id', $user->id)
            ->whereIn('section', ['five_a', 'five_b', 'five_c'])
            ->where('status', 'Active')
            ->with(['package', 'members'])
            ->get();

        // Merge all combo circles
        $combo_circles = $combo_21_circles->merge($combo_5_circles)->unique('id');
        
        // Add timer and other metadata to combo circles (similar to regular circles)
        foreach($combo_circles as $circle){
            foreach($circle->members as $member){
                // Calculate global color position based on circle section for combo packages
                $localPosition = $member->position;
                $globalColorPosition = $localPosition; // Default (for five_a or non-combo)
                
                if($circle->section === 'five_b'){
                    // five_b: local position + 5 = global color position (6-10)
                    $globalColorPosition = $localPosition + 5;
                } elseif($circle->section === 'five_c'){
                    // five_c: local position + 10 = global color position (11-15)
                    $globalColorPosition = $localPosition + 10;
                } elseif($circle->section === 'twentyone'){
                    // twentyone: local position + 15 = global color position (16-36)
                    $globalColorPosition = $localPosition + 15;
                }
                // five_a: local position = global color position (1-5), already set above
                
                $color = Color::where('package_id', $circle->package_id)->where('position', $globalColorPosition)->first();
                if($color){
                    $member['color'] = $color->color;
                }
                $member['is_downline'] = false;
                if($member->user)
                {
                    if($member->user->referal_id == $user->id || $member->user_id == $user->id){
                        $member['is_downline'] = true;
                    }
                }
            }
            
            // Add section name for frontend display - use package section name if available, otherwise use default
            $package = $circle->package;
            $section_name = null;
            if($package) {
                switch($circle->section) {
                    case 'five_a':
                        $section_name = $package->combo_five_a_name ?? '5-Member Circle 1';
                        break;
                    case 'five_b':
                        $section_name = $package->combo_five_b_name ?? '5-Member Circle 2';
                        break;
                    case 'five_c':
                        $section_name = $package->combo_five_c_name ?? '5-Member Circle 3';
                        break;
                    case 'twentyone':
                        $section_name = $package->combo_twentyone_name ?? '21-Member Circle';
                        break;
                }
            }
            $circle['section_name'] = $section_name ?? ($circle->section === 'five_a' ? '5-Member Circle 1' : ($circle->section === 'five_b' ? '5-Member Circle 2' : ($circle->section === 'five_c' ? '5-Member Circle 3' : '21-Member Circle')));

            // Add circle code for display
            $circle['circle_code'] = $circle->code ?? null;
            
            // Add timer info for 21-member combo circles (similar to regular 21-member circles)
            if($circle->section == 'twentyone'){
                $timer = Timer::where('user_id', $user->id)->where('package_id', $circle->package_id)->first();
                if($timer){
                    $purchased_at = $timer->started_at;
                    $expiresAt = $purchased_at->copy()->addDays(120);
                    $circle['purchased_at'] = $expiresAt;
                }
            }
        }

        return response()->json([
                'circles' => $all_circles,
                'combo_circles' => $combo_circles
        ],200);
    }
    
    public function get_completed_circles(Request $request)
    {
        $user = Auth::User();
        
        // Get regular completed circles
        $circles = $user->completed_circles;
        foreach($circles as $circle){
            foreach($circle->members as $member){
                $color = Color::where('package_id',$circle->package_id)->where('position',$member->position)->first();
                if($color){
                    $member['color'] = $color->color;
                }
            }
        }
        
        // Get combo completed circles (for all sections)
        $combo_circles = ComboCircle::where('user_id', $user->id)
            ->where('status', 'Completed')
            ->orderBy('id', 'desc')
            ->with(['package', 'members'])
            ->get();
        
        // Also get combo circles where user is a member (positions 1-20) that are completed
        // This handles cases where user was in upline's circle before it completed
        $comboMemberIds = ComboMember::where('user_id', $user->id)
            ->whereHas('circle', function($query) {
                $query->where('status', 'Completed');
            })
            ->selectRaw('MAX(id) as id')
            ->groupBy('combo_circle_id')
            ->pluck('id');

        $completedComboCircleIds = ComboMember::whereIn('id', $comboMemberIds)
            ->where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->pluck('combo_circle_id')
            ->unique()
            ->values();

        $member_combo_circles = ComboCircle::whereIn('id', $completedComboCircleIds)
            ->where('status', 'Completed')
            ->where('user_id', '!=', $user->id) // Only circles where user was a member, not owner
            ->with(['package', 'members'])
            ->get();

        // Merge all combo circles
        $all_combo_circles = $combo_circles->merge($member_combo_circles)->unique('id');
        
        // Format combo circles with colors and metadata
        foreach($all_combo_circles as $combo_circle){
            foreach($combo_circle->members as $member){
                // Calculate global color position based on circle section for combo packages
                $localPosition = $member->position;
                $globalColorPosition = $localPosition; // Default (for five_a or non-combo)
                
                if($combo_circle->section === 'five_b'){
                    // five_b: local position + 5 = global color position (6-10)
                    $globalColorPosition = $localPosition + 5;
                } elseif($combo_circle->section === 'five_c'){
                    // five_c: local position + 10 = global color position (11-15)
                    $globalColorPosition = $localPosition + 10;
                } elseif($combo_circle->section === 'twentyone'){
                    // twentyone: local position + 15 = global color position (16-36)
                    $globalColorPosition = $localPosition + 15;
                }
                // five_a: local position = global color position (1-5), already set above
                
                $color = Color::where('package_id', $combo_circle->package_id)->where('position', $globalColorPosition)->first();
                if($color){
                    $member['color'] = $color->color;
                }
                $member['is_downline'] = false;
                if($member->user)
                {
                    if($member->user->referal_id == $user->id || $member->user_id == $user->id){
                        $member['is_downline'] = true;
                    }
                }
            }
            
            // Add section name for frontend display - use package section name if available, otherwise use default
            $package = $combo_circle->package;
            $section_name = null;
            if($package) {
                switch($combo_circle->section) {
                    case 'five_a':
                        $section_name = $package->combo_five_a_name ?? '5-Member Circle 1';
                        break;
                    case 'five_b':
                        $section_name = $package->combo_five_b_name ?? '5-Member Circle 2';
                        break;
                    case 'five_c':
                        $section_name = $package->combo_five_c_name ?? '5-Member Circle 3';
                        break;
                    case 'twentyone':
                        $section_name = $package->combo_twentyone_name ?? '21-Member Circle';
                        break;
                }
            }
            $combo_circle['section_name'] = $section_name ?? ($combo_circle->section === 'five_a' ? '5-Member Circle 1' : ($combo_circle->section === 'five_b' ? '5-Member Circle 2' : ($combo_circle->section === 'five_c' ? '5-Member Circle 3' : '21-Member Circle')));

            // Add circle code for display
            $combo_circle['circle_code'] = $combo_circle->code ?? null;
        }

        return response()->json([
                'circles' => $circles,
                'combo_circles' => $all_combo_circles
        ],200);
    }

    public function get_downline_circle(Request $request)
    {
        $rules = [
            'package_id' => 'required|numeric|exists:packages,id',
            'downline_id' => 'required|numeric|exists:users,id',
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        $circle = Circle::where('user_id',$request->downline_id)->where('package_id',$request->package_id)->where('status','Active')->with(['package','members'])->first();
        
        if(!$circle){
            $member = Member::where('user_id', $request->downline_id)
            ->whereHas('circle', function ($query) use ($request) {
                $query->where('package_id', $request->package_id) // Ensure circle has the correct package
                        ->where('status', 'Active')
                      ->where(function ($subQuery) {
                          $subQuery->where('user_id', Auth::user()->referal_id)
                                  ->orWhereHas('user', function ($nestedQuery) {
                                      $nestedQuery->where('referal_id', Auth::user()->id);
                                  });
                      });
            })
            ->orderBy('updated_at', 'desc') // Get the latest member
            ->first();
            if(!$member){
                return response()->json([
                    'error' => 'You do not have permission to see this circle'
                ],301);
            }
            $circle = $member->circle;
        }
        $downline = User::find($request->downline_id);
        foreach($circle->members as $member){
            $color = Color::where('package_id',$circle->package_id)->where('position',$member->position)->first();
            $member['color'] = $color->color;
        }
        return response()->json([
                'circle' => $circle
        ],200);
    }
    
    public function get_logo(Request $request)
    {
        $setting = Setting::first();
        $logo = $setting->logo;
        return response()->json([
                'logo' => $logo
        ],200);
    }
    
    public function onboarding(Request $request)
    {
        $setting = Setting::first();
        return response()->json([
                'logo' => $setting->logo,
                'title' => 'Title',
                'text' => 'Lorem Ipsum'
        ],200);
    }
    
    public function create_razorpay_order(Request $request)
    {
        $rules = [
            'package_id' => 'required|numeric',
            'payment_type' => 'nullable|in:wallet,razorpay',
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        $user = Auth::User();
        $package = Package::find($request->package_id);
        
        if(!$package){
            return response()->json([
                'error' => 'Package not found'
            ], 422);
        }
        $subscription = Subscription::where('user_id',$user->id)->where('package_id',$package->id)->where('status','Active')->first();
        if($subscription){
            return response()->json([
                'error' => 'You already have purchased this package'
            ], 422);
        }

        // Check if upline has purchased this package (skip for admin - user_id = 1)
        if($user->id != 1 && $user->referal_id){
            $upline = User::find($user->referal_id);
            if($upline){
                // Check if upline has active subscription for this package
                $upline_subscription = Subscription::where('user_id', $upline->id)
                    ->where('package_id', $package->id)
                    ->where('status', 'Active')
                    ->first();

                if(!$upline_subscription){
                    // For combo packages, also check if upline has combo circles
                    if($package->is_combo){
                        $upline_combo_circle = ComboCircle::where('user_id', $upline->id)
                            ->where('package_id', $package->id)
                            ->first();

                        if(!$upline_combo_circle){
                            return response()->json([
                                'error' => 'Your upline has not purchased this package yet'
                            ], 422);
                        }
                    } else {
                        // For regular packages, check if upline has a circle
                        $upline_circle = Circle::where('user_id', $upline->id)
                            ->where('package_id', $package->id)
                            ->first();

                        if(!$upline_circle){
                            return response()->json([
                                'error' => 'Your upline has not purchased this package yet'
                            ], 422);
                        }
                    }
                }
            }
        }

        $payment_type = $request->payment_type ?? 'razorpay'; // Default to razorpay if not specified
        
        $setting = Setting::first();
        $sgst = (($setting->sgst * $package->price) / 100);
        $cgst = (($setting->cgst * $package->price) / 100);
        $total =  $sgst + $cgst + $package->price;
        
        // Handle wallet payment
        if($payment_type === 'wallet') {
            // Calculate total available balance (withdrawable + non-withdrawable)
            $wallet_balance = $user->wallet ?? 0;
            $not_withdraw_balance = $user->not_withdraw_amount ?? 0;
            $total_available = $wallet_balance + $not_withdraw_balance;
            
            // Check if user has sufficient balance (from both wallet and not_withdraw_amount)
            if($total_available < $total) {
                return response()->json([
                    'error' => 'Insufficient wallet balance. Required: ₹' . number_format($total, 2) . ', Available: ₹' . number_format($total_available, 2) . ' (Withdrawable: ₹' . number_format($wallet_balance, 2) . ', Non-withdrawable: ₹' . number_format($not_withdraw_balance, 2) . ')'
                ], 422);
            }
            
            // Create order
            $order = new Order();
            $order->user_id = $user->id;
            $order->package_id = $package->id;
            $order->order_id = 'wallet_' . uniqid() . time();
            $order->payment_id = 'wallet_payment_' . uniqid();
            $order->amount = $total;
            $order->payment_method = 'wallet';
            $order->status = 'Verified';
            $order->save();
            
            // Deduct from not_withdraw_amount first, then from wallet
            $remaining_amount = $total;
            
            // First, deduct from not_withdraw_amount (if available)
            if($not_withdraw_balance > 0 && $remaining_amount > 0) {
                if($not_withdraw_balance >= $remaining_amount) {
                    // All amount can be deducted from not_withdraw_amount
                    $user->not_withdraw_amount = $not_withdraw_balance - $remaining_amount;
                    $remaining_amount = 0;
                } else {
                    // Deduct all from not_withdraw_amount, remainder from wallet
                    $remaining_amount = $remaining_amount - $not_withdraw_balance;
                    $user->not_withdraw_amount = 0;
                }
            }
            
            // Then, deduct remaining amount from wallet (if any)
            if($remaining_amount > 0) {
                $user->wallet = $wallet_balance - $remaining_amount;
            }
            
            $user->save();
            
            // Create transaction
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->type = 'Debit';
            $transaction->reason = $package->name . ' Package Purchased (Wallet)';
            $transaction->amount = $total;
            // Calculate total balance after deduction
            $total_balance_after = ($user->wallet ?? 0) + ($user->not_withdraw_amount ?? 0);
            $transaction->balance = $total_balance_after;
            $transaction->save();
            
            // Complete the purchase
            return $this->complete_package_purchase($user, $package, $order);
        }
        
        // Bypass Razorpay for local development (Expo Go doesn't support Razorpay)
        if (app()->environment('local')) {
            // Create order with dummy data for local development
            $dummyOrderId = 'dummy_order_' . uniqid() . '_' . time();
            $dummyPaymentId = 'dummy_payment_' . uniqid();

            $order = new Order();
            $order->user_id = $user->id;
            $order->package_id = $package->id;
            $order->order_id = $dummyOrderId;
            $order->payment_id = $dummyPaymentId;
            $order->amount = $total;
            $order->payment_method = 'razorpay';
            $order->status = 'Verified'; // Mark as verified directly for local
            $order->save();

            // Create subscription
            $subscription = new Subscription();
            $subscription->user_id = $user->id;
            $subscription->package_id = $package->id;
            $subscription->status = 'Active';
            $subscription->save();
            
            // Handle circle logic
            if($package->is_combo){
                $this->handle_combo_package($user, $package->id);
            } elseif($package->total_members == 5){
                // Handle 5-member circle logic separately
                $this->handle_5_member_circle($user, $package->id);
            } else {
                // Existing logic for 2, 3, 4 downline circles - DO NOT CHANGE
                $member = Member::where('user_id',$user->referal_id)->first();
                if($member){
                    $circle = Circle::where('package_id',$package->id)->where('user_id',$member->user_id)->where('status','Active')->first();
                    if($circle){
                        $this->fill($circle->id,$package->id,$user->id);
                    }else{
                        $circle = $this->findUplineCircle($user, $package->id);
                    
                        if($circle){
                            $condition = 1;
                            $this->fill_directly($circle->id,$package->id,$user->id,$condition);
                        }else{
                            $user->create_circle($package->id);
                        }
                    }
                }else{
                    $user->create_circle($package->id);
                }
            }
            
            // Create transaction record for dummy payment
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->type = 'Debit';
            $transaction->reason = $package->name . ' Package Purchased (Razorpay - Local Dummy)';
            $transaction->amount = $total;
            $transaction->balance = $user->wallet ?? 0;
            $transaction->save();
            
            // Create timer
            $timer = new Timer();
            $timer->user_id = $user->id;
            $timer->package_id = $package->id;
            $timer->started_at = now();
            $timer->save();
            
            // Send email
            $info = array(
                'package' => $package,
                'user' => $user
            );
                
            try {
                Mail::send('email.package_purchased', ['info' => $info], function ($message) use ($user) {
                    $message->to($user->email, $user->name)
                            ->subject('Embark on a Remarkable Journey with Allons-Z!');
                });
            } catch (\Exception $e) {
                // Email failure should not break the purchase
            }
            
            return response()->json([
                'message' => 'Package purchased successfully (Local Dummy Payment)',
                'order' => $order,
                'subscription' => $subscription
            ], 200);
        }
        
        // Continue with Razorpay payment flow for production
        $item_name = $package->name;
        $item_number = $package->id;
        $item_amount = $package->price;

        $orderData = [
            'receipt'         => strval($item_number),
            'amount'          => $total * 100, // 2000 rupees in paise
            'currency'        => 'INR',
            'payment_capture' => 1 // auto capture
        ];
        
        $razorpayOrder = $this->api->order->create($orderData);
        $razorpayOrderId = $razorpayOrder['id'];
        
        $displayAmount = $amount = $orderData['amount'];
                    
        if ($this->displayCurrency !== 'INR')
        {
            $url = "https://api.fixer.io/latest?symbols=$this->displayCurrency&base=INR";
            $exchange = json_decode(file_get_contents($url), true);
                    
            $displayAmount = $exchange['rates'][$this->displayCurrency] * $amount / 100;
        }
                    
        $data = [
            "key"               => $this->keyId,
            "amount"            => $amount,
            "name"              => $item_name,
            "description"       => $item_name,
            "prefill"           => [
    			"name"              => $user->name,
    			"email"             => $user->email,
    			"contact"           => $user->phone,
            ],
            "notes"             => [
				"address"           => $user->address,
				"merchant_order_id" => $item_number,
            ],
            "theme"             => [
				"color"             => "#3399cc"
            ],
            "order_id"          => $razorpayOrderId,
        ];
                    
        if ($this->displayCurrency !== 'INR')
        {
            $data['display_currency']  = $this->displayCurrency;
            $data['display_amount']    = $displayAmount;
        }
                    
        $displayCurrency = $this->displayCurrency;
        
        $order = new Order();
        $order->user_id = $user->id;
        $order->package_id = $package->id;
        $order->order_id = $razorpayOrderId;
        $order->amount = $total;
        $order->payment_method = 'razorpay';
        $order->status = 'Created';
        $order->save();
        
        return response()->json([
                'data' => $data,
                'displayCurrency' => $displayCurrency
        ],200);
    }
    
    /**
     * Helper method to complete package purchase (subscription, circle, timer, email)
     * This is called after payment is verified (either wallet or Razorpay)
     */
    private function complete_package_purchase($user, $package, $order)
    {
        // Create subscription
        $subscription = new Subscription();
        $subscription->user_id = $user->id;
        $subscription->package_id = $package->id;
        $subscription->status = 'Active';
        $subscription->save();
        
        // Handle circle creation based on package type
        if($package->is_combo){
            $this->handle_combo_package($user, $package->id);
        } elseif($package->total_members == 5){
            // Handle 5-member circle logic separately
            $this->handle_5_member_circle($user, $package->id);
        } else {
            // Existing logic for 2, 3, 4 downline circles - DO NOT CHANGE
            $member = Member::where('user_id',$user->referal_id)->first();
            if($member){
                $circle = Circle::where('package_id',$package->id)->where('user_id',$member->user_id)->where('status','Active')->first();
                if($circle){
                    $this->fill($circle->id,$package->id,$user->id);
                }else{
                    $circle = $this->findUplineCircle($user, $package->id);
                
                    if($circle){
                        $condition = 1;
                        $this->fill_directly($circle->id,$package->id,$user->id,$condition);
                    }else{
                        $user->create_circle($package->id);
                    }
                }
            }else{
                $user->create_circle($package->id);
            }
        }
        
        // Create timer
        $timer = new Timer();
        $timer->user_id = $user->id;
        $timer->package_id = $package->id;
        $timer->started_at = now();
        $timer->save();
        
        // Send email
        $info = array(
            'package' => $package,
            'user' => $user
        );
            
        try {
            Mail::send('email.package_purchased', ['info' => $info], function ($message) use ($user) {
                $message->to($user->email, $user->name)
                        ->subject('Embark on a Remarkable Journey with Allons-Z!');
            });

            if (Mail::failures()) {
                return response()->json([
                        'error' => 'Failed to send email'
                ],422);
            }

        } catch (\Exception $e) {
            return response()->json([
                        'error' => 'Failed to send email'
            ],422);
        }
        
        return response()->json([
                'message' => 'Package purchased successfully'
        ],200);
    }
    public function verify_razorpay_signature(Request $request)
    {
        $rules = [
            'razorpay_order_id' => 'required',
            'razorpay_payment_id' => 'required',
            'razorpay_signature' => 'required',
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        $user = Auth::User();
        $order = Order::where('order_id',$request->razorpay_order_id)->where('status','Created')->first();

        if(!$order){
            return response()->json([
                    'error' => 'Order id not found',
            ],422);
        }
        $success = true;
        $error = "Payment Failed";
        
        
        $razorpay_order_id = $request->razorpay_order_id;
        $razorpay_payment_id = $request->razorpay_payment_id;
        $razorpay_signature = $request->razorpay_signature;
        
        try {
            $attributes = array(
                'razorpay_order_id' => $razorpay_order_id,
                'razorpay_payment_id' => $razorpay_payment_id,
                'razorpay_signature' => $razorpay_signature
            );
            $this->api->utility->verifyPaymentSignature($attributes);
        }
        catch(SignatureVerificationError $e){
            $success = false;
            $error = 'Razorpay Error : ' . $e->getMessage();
            return response()->json([
                    'error' => $error
            ],400);
        }
        
        if ($success === true)
        {
            $razorpayOrder = $this->api->order->fetch($razorpay_order_id);
            $reciept = $razorpayOrder['receipt'];
            $transaction_id = $razorpay_payment_id;
            
            $order->payment_id = $razorpay_payment_id;
            $order->signature = $razorpay_signature;
            $order->status = 'Verified';
            $order->save();
            
            $package = Package::find($order->package_id);
            
            // Calculate total amount with GST for transaction record
            $setting = Setting::first();
            $sgst = (($setting->sgst * $package->price) / 100);
            $cgst = (($setting->cgst * $package->price) / 100);
            $total = $sgst + $cgst + $package->price;
            
            // Create transaction record for Razorpay payment (no wallet deduction)
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->type = 'Debit';
            $transaction->reason = $package->name .' Package Purchased (Razorpay)';
            $transaction->amount = $total; // Total amount including GST
            $transaction->balance = $user->wallet; // Wallet balance remains unchanged
            $transaction->save();
            
            // Complete the purchase using helper method
            return $this->complete_package_purchase($user, $package, $order);

        }
        else
        {
            return response()->json([
                    'error' => $error
            ],422);
        }
    }

    public function create_circle_manually(Request $request)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'order_id' => 'required|exists:orders,order_id',
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        $user = User::find($request->user_id);
        $order = Order::where('order_id',$request->order_id)->first();
        if(!$order){
            return response()->json([
                    'error' => 'Order id not found',
            ],422);
        }

            
            $package = Package::find($order->package_id);
            
            // Check if this is a 5-member circle (simple circle)
            if($package->total_members == 5){
                // Handle 5-member circle logic separately
                $this->handle_5_member_circle($user, $package->id);
            } else {
                // Existing logic for 2, 3, 4 downline circles - DO NOT CHANGE
                $member = Member::where('user_id',$user->referal_id)->first();
                if($member){
                    $circle = Circle::where('package_id',$package->id)->where('user_id',$member->user_id)->where('status','Active')->first();
                    if($circle){
                        $this->fill($circle->id,$package->id,$user->id);
                    }else{
                        $circle = $this->findUplineCircle($user, $package->id);
                    
                        if($circle){
                            $condition = 1;
                            $this->fill_directly($circle->id,$package->id,$user->id,$condition);
                        }else{
                            $user->create_circle($package->id);
                        }
                    }
                }else{
                    $user->create_circle($package->id);
                    
                }
            }
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->type = 'Debit';
            $transaction->reason = $package->name .' Package Purchased';
            $transaction->amount = $package->price;
            $transaction->balance = $user->wallet;
            $transaction->save();

            $referal = $user->referal;
            // if($referal){
            //     $this->check_referal($referal->id,$package->id,Auth::User()->id);
            // }

            $timer = new Timer();
            $timer->user_id = $user->id;
            $timer->package_id = $package->id;
            $timer->started_at = now();
            $timer->save();
            
            $info = array(
                'package' => $package,
                'user' => $user
            );
                
            try {
                Mail::send('email.package_purchased', ['info' => $info], function ($message) use ($user) {
                    $message->to($user->email, $user->name)
                            ->subject('Embark on a Remarkable Journey with Allons-Z!');
                });

                if (Mail::failures()) {
                    return response()->json([
                            'error' => 'Failed to send email'
                    ],422);
                }

            } catch (\Exception $e) {
                return response()->json([
                    'error' => $e->getMessage()
                ],422);
            }
            
            return response()->json([
                    'message' => 'Package purchased successfully'
            ],200);

    }
    
    public function check_referal($user_id,$package_id,$current_user_id = null){
        // Log::info("checking referal of -user_id ". $current_user_id);
        if(!$current_user_id){
            $current_user_id = Auth::User()->id;
        }
        $user = User::find($user_id);
        $user->update_circle_member($package_id,$current_user_id);
        $referal = $user->referal;
        if($referal){
            $this->check_referal($referal->id,$package_id,$current_user_id);
        }
    }
    
    public function my_trips(Request $request)
    {
        $user = Auth::User();
        $trips = Trip::where('user_id',$user->id)->with(['tour','photos','rewards'])->get();
        return response()->json([
            'trips' => $trips
        ],200);
    }
    
    public function request_trip(Request $request)
    {
        $rules = [
            'tour_id' => 'required|exists:tours,id',
            'from_date' => 'required',
            'to_date' => 'required',
            'members' => 'required',
            'from_place' => 'required',
            
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        $user = Auth::User();
        $trip = new Trip();
        $trip->user_id = $user->id;
        $trip->tour_id = $request->tour_id;
        $trip->from_date = $request->from_date;
        $trip->to_date = $request->to_date;
        $trip->members = $request->members;
        $trip->from_place = $request->from_place;
        $trip->status = 'Pending';
        $trip->save();
        return response()->json([
            'message' => 'Trip request sent successfully'
        ],200);
    }
    
      public function update_trip(Request $request)
    {
        $rules = [
            'tour_id' => 'required|exists:tours,id',
             'trip_id'  => 'required|exists:trips,id',
            'from_date' => 'required',
            'to_date' => 'required',
            'members' => 'required',
            'from_place' => 'required',
            
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        $user = Auth::User();
        $trip = Trip::find($request->trip_id);
        $trip->user_id = $user->id;
        $trip->tour_id = $request->tour_id;
        $trip->from_date = $request->from_date;
        $trip->to_date = $request->to_date;
        $trip->members = $request->members;
        $trip->from_place = $request->from_place;
        $trip->status = 'Pending';
        $trip->save();
        return response()->json([
            'message' => 'Trip updated successfully'
        ],200);
    }

    
    public function upload_trip_photo(Request $request)
    {
        $rules = [
            'trip_id' => 'required|exists:trips,id',
            'photos' => 'required|array',
            'photos.*' => 'image|mimes:jpeg,jpg,png|max:2048',
        ];
    
        $validation = \Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors()->first()
            ], 422);
        }
    
        $user = Auth::user();
        $trip = Trip::find($request->trip_id);
        $uploadedPhotos = [];
    
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $file) {
                $extension = $file->getClientOriginalExtension();
                $filename = uniqid('trip_', true) . '.' . $extension;
    
                $file->move(public_path('/images/tours'), $filename);

                $photo = new Photo();
                $photo->user_id = $user->id;
                $photo->trip_id = $request->trip_id;
                $photo->tour_id = $trip->tour_id;
                $photo->photo = $filename;
                $photo->save();
    
                $uploadedPhotos[] = [
                    'photo_id' => $photo->id,
                    'photo_url' => url('/images/tours/' . $filename)
                ];
            }
        }
    
        return response()->json([
            'message' => 'Trip photos uploaded successfully',
            'uploaded_photos' => $uploadedPhotos
        ], 200);
    }

    
    public function withdraw_request(Request $request)
    {
        $rules = [
            'amount' => 'required|integer|min:1',
        ];
        
        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }
        
        $user = Auth::User();
        
        // Calculate available balance (wallet minus pending withdrawals)
        $pending_withdrawals = Withdraw::where('user_id', $user->id)
            ->where('status', 'Pending')
            ->sum('amount');
        $available_balance = $user->wallet - $pending_withdrawals;
        
        if($available_balance < $request->amount){
            return response()->json([
                'error' => 'Insufficient wallet balance'
            ],422);
        }
        
        // Don't deduct wallet here - only deduct when approved by admin
        // This prevents wallet balance issues if request is rejected
        // The available balance check above ensures user can't request more than available
        
        $withdraw = new Withdraw();
        $withdraw->user_id = Auth::User()->id;
        $withdraw->amount = $request->amount;
        $withdraw->request_code = 'RQT'.rand(100000,999999);
        $withdraw->status = 'Pending';
        $withdraw->save();
        
        return response()->json([
            'message' => 'Withdraw Request sent successfully'
        ],200);
        
    }

    public function combo_withdraw_request(Request $request)
    {
        $rules = [
            'amount' => 'required|integer|min:1',
        ];

        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }

        $user = Auth::User();

        $combo_package_ids = Package::where('is_combo', true)->pluck('id');
        $direct_referrals_count = User::where('referal_id', $user->id)
            ->whereHas('subscriptions', function($query) use ($combo_package_ids) {
                $query->whereIn('subscriptions.package_id', $combo_package_ids)
                      ->where('subscriptions.status', 'Active');
            })
            ->count();

        if($direct_referrals_count < 4){
            return response()->json([
                'error' => 'Combo wallet withdraw requires 4 direct referrals'
            ],422);
        }

        $pending_withdrawals = Withdraw::where('user_id', $user->id)
            ->where('wallet_type', 'combo')
            ->where('status', 'Pending')
            ->sum('amount');

        // Calculate locked amount for auto-renewal (reserved for circle renewals)
        $locked_autorenew_amount = $this->combo_get_locked_autorenew_amount($user->id);

        // Withdrawable = Total wallet - Pending withdrawals - Locked auto-renewal amount
        $available_balance = ($user->combo_wallet ?? 0) - $pending_withdrawals - $locked_autorenew_amount;

        if($available_balance < $request->amount){
            return response()->json([
                'error' => 'Withdraw limit reached for auto renewal'
            ],422);
        }

        $withdraw = new Withdraw();
        $withdraw->user_id = $user->id;
        $withdraw->amount = $request->amount;
        $withdraw->wallet_type = 'combo';
        $withdraw->request_code = 'RQT'.rand(100000,999999);
        $withdraw->status = 'Pending';
        $withdraw->save();

        return response()->json([
            'message' => 'Combo wallet withdraw request sent successfully'
        ],200);
    }
    
    public function withdraw_history()
    {
        $user = Auth::User();
        $withdraws = $user->withdraws()->orderBy('created_at', 'desc')->get();
        return response()->json([
            'withdraws' => $withdraws
        ],200);
        
    }
    
    public function transaction_history()
    {
        $user = Auth::User();
        $transactions = $user->transactions;
        return response()->json([
            'transactions' => $transactions
        ],200);
        
    }
    
    public function get_add()
    {
        $setting = Setting::select('add_type','add_url')->first();
        return response()->json([
            'adds' => $setting
        ],200);

    }

    public function get_auto_renew_status()
    {
        $user = Auth::User();
        return response()->json([
            'auto_renew' => $user->auto_renew
        ],200);
    }

    public function toggle_auto_renew(Request $request)
    {
        $rules = [
            'auto_renew' => 'required|boolean',
        ];

        $validation = \Validator::make($request->all(), $rules);
        $error = $validation->errors()->first();
        if ($error) {
            return response()->json([
                'error' => $error
            ], 422);
        }

        $user = Auth::User();
        $user->auto_renew = $request->auto_renew;
        $user->save();

        return response()->json([
            'msg' => 'Auto-renewal setting updated successfully',
            'auto_renew' => $user->auto_renew
        ],200);
    }

    // User API: Check if user's timer has less than 1 month remaining (returns alert status)
    // Note: Expired timers are NOT shown to users, only shown in admin panel
    public function check_timer_alert()
    {
        $user = Auth::User();
        $timers_alert = [];

        // Get all timers for the authenticated user
        $timers = Timer::where('user_id', $user->id)->with('package')->get();

        foreach($timers as $timer){
            $started_at = $timer->started_at;
            $expires_at = $started_at->copy()->addDays(120); // 4 months = 120 days
            $now = now();

            // Calculate days remaining
            $days_remaining = $now->diffInDays($expires_at, false);

            // Only show alert if timer has NOT expired and has 30 days or less remaining
            // Expired timers are hidden from users as per requirement
            if($days_remaining <= 30 && $days_remaining >= 0){
                $timers_alert[] = [
                    'package_id' => $timer->package_id,
                    'package_name' => $timer->package ? $timer->package->name : 'N/A',
                    'started_at' => $started_at->format('Y-m-d H:i:s'),
                    'expires_at' => $expires_at->format('Y-m-d H:i:s'),
                    'days_remaining' => ceil($days_remaining),
                    'alert_message' => "Your travel package expires in " . ceil($days_remaining) . " day(s). Please complete your travel soon!"
                ];
            }
        }

        return response()->json([
            'has_alert' => count($timers_alert) > 0,
            'timers' => $timers_alert
        ],200);
    }
    
    public function get_timer()
    {
        // $timer = Auth::user()->active_circles()->shuffle()->first();
        // $timer = Circle::where('user_id',Auth::User()->id)->inRandomOrder()->where('status','Active')->select('created_at')->first();
        // if(!$timer){
        //     $timer = Circle::first();
        // }
        $circles = [];
        $members = Member::where('user_id',Auth::User()->id)->with('circle.members')->get();
        foreach($members as $member){
            $circle = $member->circle;
            $timer = Timer::where('user_id',Auth::User()->id)->where('package_id',$member->package_id)->first();
            $purchased_at = $timer->started_at;
            $expiresAt = $purchased_at->copy()->addDays(120); // Changed from 60 to 120 days (4 months)
            $member['purchased_at'] = $expiresAt;

            foreach($circle->members as $new_member){
                $color = Color::where('package_id',$new_member->circle->package_id)->where('position',$new_member->position)->first();
                $new_member['color'] = $color->color;
                $new_member['is_downline'] = false;
                if($new_member->user)
                {
                    if($new_member->user->referal_id == Auth::User()->id || $new_member->user_id == Auth::User()->id){
                        $new_member['is_downline'] = true;
                    }
                }
                $new_member->makeHidden(['circle']);
            }
        }
        return response()->json([
            'timers' => $members
        ],200);
        
    }
    
    // public function create_circle(Request $request)
    public function create_circle(Request $request)
    {
        if ($request->clear_data == 1) {
            // Empty related tables
            Circle::truncate();
            CircleReward::truncate();
            Order::truncate();
            Subscription::truncate();
            Member::truncate();
            Transaction::truncate();
            Trip::truncate();
        }

        $user = User::where('username', $request->username)->first();
        if (!$user) {
            return response()->json([
                'msg' => 'User not found'
            ], 404);
        }
        $current_user = $user;
        $user_id = $current_user->id;

        $package = Package::find($request->package_id);
        // $package = Package::find($package_id);
        if (!$package) {
            return response()->json([
                'msg' => 'Package not found'
            ], 404);
        }
        
        // Check if this is a 5-member circle (simple circle)
        if($package->total_members == 5){
            // Handle 5-member circle logic separately
            $this->handle_5_member_circle($user, $package->id);
        } else {
            // Existing logic for 2, 3, 4 downline circles - DO NOT CHANGE
            $member = Member::where('user_id',$user->referal_id)->orderBy('circle_id','desc')->first();
            if($member){
                $circle = Circle::where('package_id',$package->id)->where('user_id',$member->user_id)->with('package')->first();
                $this->fill($circle->id,$package->id,$user->id);
            }else{
                $circle = $this->findUplineCircle($user, $package->id);
                if($circle){
                    $this->fill_directly($circle->id,$package->id,$user->id);
                }else{
                    $user->create_circle($package->id);
                }
                    
            }
        }
        
        $subscription = new Subscription();
        $subscription->user_id = $user->id;
        $subscription->package_id = $package->id;
        $subscription->status = 'Active';
        $subscription->save();
        return response()->json([
            'msg' => 'Success'
        ], 200);
    }
    
    public function fill($circle_id,$package_id,$user_id)
    {
        $circle = Circle::find($circle_id);
        $current_user = User::find($user_id);
        $package = Package::find($package_id);
        if (!$package) {
            // Log::info('No Package Found');
        }
        $max_downlines = $package->max_downlines;
        switch ($max_downlines) {
            case 2:
            $jump_position = 7;
            break;
            case 3:
            $jump_position = 13;
            break;
            case 4:
            $jump_position = 21;
            break;
            default:
            return response()->json(['error' => 'Invalid max_downline value'], 400);
        }
        
        $occupied_count = Member::where('circle_id', $circle->id)->where('status', 'Occupied')->count();
                $first_position = Member::where('circle_id', $circle->id)->where('position', 1)->first();
                $second_position = Member::where('circle_id', $circle->id)->where('position', 2)->first();
                $third_position = Member::where('circle_id', $circle->id)->where('position', 3)->first();
                $fourth_position = Member::where('circle_id', $circle->id)->where('position', 4)->first();
                $fifth_position = Member::where('circle_id', $circle->id)->where('position', 5)->first();
                $six_position = Member::where('circle_id', $circle->id)->where('position', 6)->first();
                $seven_position = Member::where('circle_id', $circle->id)->where('position', 7)->first();
                $eight_position = Member::where('circle_id', $circle->id)->where('position', 8)->first();
                $nine_position = Member::where('circle_id', $circle->id)->where('position', 9)->first();
                $ten_position = Member::where('circle_id', $circle->id)->where('position', 10)->first();
                $eleven_position = Member::where('circle_id', $circle->id)->where('position', 11)->first();
                $twelve_position = Member::where('circle_id', $circle->id)->where('position', 12)->first();
                $thirteen_position = Member::where('circle_id', $circle->id)->where('position', 13)->first();
                $fourteen_position = Member::where('circle_id', $circle->id)->where('position', 14)->first();
                $fifteen_position = Member::where('circle_id', $circle->id)->where('position', 15)->first();
                $sixteen_position = Member::where('circle_id', $circle->id)->where('position', 16)->first();
                $seventeen_position = Member::where('circle_id', $circle->id)->where('position', 17)->first();
                $eighteen_position = Member::where('circle_id', $circle->id)->where('position', 18)->first();
                $nineteen_position = Member::where('circle_id', $circle->id)->where('position', 19)->first();
                $twenty_position = Member::where('circle_id', $circle->id)->where('position', 20)->first();
                $twenty_one_position = Member::where('circle_id', $circle->id)->where('position', 21)->first();
                        
                foreach($circle->members as $member){
                    if($jump_position == 7){
                        if($occupied_count == 1 && $current_user->referal_id == $first_position->user_id){
                            $third_position->user_id = $first_position->user_id;
                            $third_position->status = 'Occupied';
                            $third_position->save();
                            
                            $first_position->user_id = $user_id;
                            $first_position->status = 'Occupied';
                            $first_position->save();
                            break;
                        }
                        if($occupied_count == 2 && $current_user->referal_id == $first_position->user_id || $occupied_count == 2 && $current_user->referal_id == $third_position->user_id){
                            $seven_position->user_id = $third_position->user_id;
                            $seven_position->status = 'Occupied';
                            $seven_position->save();
                            
                            $third_position->user_id = $first_position->user_id;
                            $third_position->status = 'Occupied';
                            $third_position->save();
                            
                            $six_position->user_id = $user_id;
                            $six_position->status = 'Occupied';
                            $six_position->save();
                            
                            $first_position->user_id = null;
                            $first_position->status = 'Empty';
                            $first_position->save();
                            break;
                        }
                        if ($occupied_count > 2 && $occupied_count < 7) {
                            if ($current_user->referal_id == $six_position->user_id || $current_user->referal_id == $fifth_position->user_id) {
                                // Log::info("current-user is ". $current_user->username .", in section 2 of - ".$circle->user->username);
                                $this->assignPosition([$fifth_position, $fourth_position], $user_id);
                                break;
                            } elseif ($current_user->referal_id == $third_position->user_id || $current_user->referal_id == $first_position->user_id) {
                                // Log::info("current-user is ". $current_user->username .", in section 1 of - ".$circle->user->username);
                                $this->assignPosition([$first_position, $second_position], $user_id);
                                break;
                            } elseif ($current_user->referal_id == $seven_position->user_id) {
                                // Log::info("current-user is ". $current_user->username .", in section any of - ".$circle->user->username);
                                $this->assignPosition([$first_position, $second_position, $fifth_position,$fourth_position], $user_id);
                                break;
                            } else {
                                //
                            }
                        }
                    }
                    
                    if($jump_position == 13){
                        if($occupied_count == 1 && $current_user->referal_id == $first_position->user_id){
                            $fourth_position->user_id = $first_position->user_id;
                            $fourth_position->status = 'Occupied';
                            $fourth_position->save();
                            
                            $first_position->user_id = $user_id;
                            $first_position->status = 'Occupied';
                            $first_position->save();
                            break;
                        }
                        if($occupied_count == 2 && $current_user->referal_id == $first_position->user_id || $occupied_count == 2 && $current_user->referal_id == $fourth_position->user_id){
                            $second_position->user_id = $user_id;
                            $second_position->status = 'Occupied';
                            $second_position->save();
                            break;
                        }
                        if($occupied_count == 3 && $current_user->referal_id == $first_position->user_id || $occupied_count == 3 && $current_user->referal_id == $second_position->user_id 
                            || $occupied_count == 3 && $current_user->referal_id == $fourth_position->user_id){
                                
                            $thirteen_position->user_id = $fourth_position->user_id;
                            $thirteen_position->status = 'Occupied';
                            $thirteen_position->save();
                            
                            $fourth_position->user_id = $first_position->user_id;
                            $fourth_position->status = 'Occupied';
                            $fourth_position->save();
                            
                            $eight_position->user_id = $second_position->user_id;
                            $eight_position->status = 'Occupied';
                            $eight_position->save();
                            
                            $twelve_position->user_id = $user_id;
                            $twelve_position->status = 'Occupied';
                            $twelve_position->save();
                            
                            $first_position->user_id = null;
                            $first_position->status = 'Empty';
                            $first_position->save();
                            
                            $second_position->user_id = null;
                            $second_position->status = 'Empty';
                            $second_position->save();
                            
                            $third_position->user_id = null;
                            $third_position->status = 'Empty';
                            $third_position->save();
                            
                            break;
                        }
                        if ($occupied_count > 3 && $occupied_count < 13) {
                            if ($current_user->referal_id == $first_position->user_id || $current_user->referal_id == $second_position->user_id
                                || $current_user->referal_id == $fourth_position->user_id) {
                                // Log::info("current-user is ". $current_user->username .", in section 1 of - ".$circle->user->username);
                                $this->assignPosition([$first_position, $second_position, $third_position], $user_id);
                                break;
                            } elseif ($current_user->referal_id == $fifth_position->user_id || $current_user->referal_id == $six_position->user_id 
                                || $current_user->referal_id == $eight_position->user_id) {
                                // Log::info("current-user is ". $current_user->username .", in section 2 of - ".$circle->user->username);
                                $this->assignPosition([$fifth_position, $six_position,$seven_position], $user_id);
                                break;
                            } elseif ($current_user->referal_id == $nine_position->user_id || $current_user->referal_id == $ten_position->user_id 
                                || $current_user->referal_id == $twelve_position->user_id) {
                                // Log::info("current-user is ". $current_user->username .", in section 2 of - ".$circle->user->username);
                                $this->assignPosition([$eleven_position, $ten_position,$nine_position], $user_id);
                                break;
                            } elseif ($current_user->referal_id == $thirteen_position->user_id) {
                                // Log::info("current-user is ". $current_user->username .", in section any of - ".$circle->user->username);
                                $this->assignPosition([$first_position, $second_position, $third_position,$fifth_position, $six_position, $seven_position,
                                $eleven_position, $ten_position, $nine_position], $user_id);
                                break;
                            } else {
                                //
                            }
                        }
                    }
                    
                    if($jump_position == 21){
                        if($occupied_count == 1 && $current_user->referal_id == $first_position->user_id){
                            $fifth_position->user_id = $first_position->user_id;
                            $fifth_position->status = 'Occupied';
                            $fifth_position->save();
                            
                            $first_position->user_id = $user_id;
                            $first_position->status = 'Occupied';
                            $first_position->save();
                            break;
                        }
                        if($occupied_count == 2 && $current_user->referal_id == $first_position->user_id || $occupied_count == 2 && $current_user->referal_id == $fifth_position->user_id){
                            $second_position->user_id = $user_id;
                            $second_position->status = 'Occupied';
                            $second_position->save();
                            break;
                        }
                        if($occupied_count == 3 && $current_user->referal_id == $first_position->user_id || $occupied_count == 3 && $current_user->referal_id == $second_position->user_id
                            || $occupied_count == 3 && $current_user->referal_id == $fifth_position->user_id){
                            $third_position->user_id = $user_id;
                            $third_position->status = 'Occupied';
                            $third_position->save();
                            break;
                        }
                        if($occupied_count == 4 && $current_user->referal_id == $first_position->user_id || $occupied_count == 4 && $current_user->referal_id == $second_position->user_id 
                            || $occupied_count == 4 && $current_user->referal_id == $third_position->user_id || $occupied_count == 4 && $current_user->referal_id == $fifth_position->user_id){
                            $twenty_one_position->user_id = $fifth_position->user_id;
                            $twenty_one_position->status = 'Occupied';
                            $twenty_one_position->save();
                            
                            $fifth_position->user_id = $first_position->user_id;
                            $fifth_position->status = 'Occupied';
                            $fifth_position->save();
                            
                            $ten_position->user_id = $second_position->user_id;
                            $ten_position->status = 'Occupied';
                            $ten_position->save();
                            
                            $fifteen_position->user_id = $user_id;
                            $fifteen_position->status = 'Occupied';
                            $fifteen_position->save();
                            
                            $twenty_position->user_id = $third_position->user_id;
                            $twenty_position->status = 'Occupied';
                            $twenty_position->save();
                            
                            $first_position->user_id = null;
                            $first_position->status = 'Empty';
                            $first_position->save();
                            
                            $second_position->user_id = null;
                            $second_position->status = 'Empty';
                            $second_position->save();
                            
                            $third_position->user_id = null;
                            $third_position->status = 'Empty';
                            $third_position->save();
                            
                            $fourth_position->user_id = null;
                            $fourth_position->status = 'Empty';
                            $fourth_position->save();
                            break;
                        }
                        
                        if ($occupied_count > 4 && $occupied_count < 21) {
                            if ($current_user->referal_id == $first_position->user_id || $current_user->referal_id == $second_position->user_id 
                                || $current_user->referal_id == $third_position->user_id || $current_user->referal_id == $fifth_position->user_id) {
                                    
                                $this->assignPosition([$first_position, $second_position, $third_position,$fourth_position], $user_id);
                                break;
                                
                            } elseif ($current_user->referal_id == $six_position->user_id || $current_user->referal_id == $seven_position->user_id 
                                || $current_user->referal_id == $eight_position->user_id || $current_user->referal_id == $ten_position->user_id) {
                                    
                                $this->assignPosition([$six_position,$seven_position,$eight_position,$nine_position], $user_id);
                                break;
                                
                            } elseif ($current_user->referal_id == $eleven_position->user_id || $current_user->referal_id == $twelve_position->user_id 
                                || $current_user->referal_id == $thirteen_position->user_id || $current_user->referal_id == $fifteen_position->user_id) {
                                    
                                $this->assignPosition([$fourteen_position, $thirteen_position,$twelve_position,$eleven_position], $user_id);
                                break;
                                
                            } elseif ($current_user->referal_id == $sixteen_position->user_id || $current_user->referal_id == $seventeen_position->user_id 
                                || $current_user->referal_id == $eighteen_position->user_id || $current_user->referal_id == $twenty_position->user_id) {
                                    
                                $this->assignPosition([$nineteen_position, $eighteen_position, $seventeen_position,$sixteen_position], $user_id);
                                break;
                                
                            } elseif ($current_user->referal_id == $twenty_one_position->user_id) {
                                    
                                $this->assignPosition([$first_position, $second_position,$third_position,$fourth_position,$six_position,$seven_position,$eight_position,$nine_position,
                                $nineteen_position, $eighteen_position, $seventeen_position,$sixteen_position,$fourteen_position, $thirteen_position,$twelve_position,$eleven_position], $user_id);
                                break;
                            } else {
                                //
                            }
                        }
                    }
                }
        $this->is_section_completed($circle_id,$package_id,$user_id);
        $this->is_circle_completed($circle_id,$package_id,$user_id);
    }
    
    public function fill_directly($circle_id,$package_id,$user_id,$condition = 0)
    {
        $circle = Circle::find($circle_id);
        $current_user = User::find($user_id);
        $package = Package::find($package_id);
        if (!$package) {
            //Log::info('No Package Found');
        }
        $max_downlines = $package->max_downlines;
        switch ($max_downlines) {
            case 2:
            $jump_position = 7;
            break;
            case 3:
            $jump_position = 13;
            break;
            case 4:
            $jump_position = 21;
            break;
            default:
            return response()->json(['error' => 'Invalid max_downline value'], 400);
        }
        
        $occupied_count = Member::where('circle_id', $circle->id)->where('status', 'Occupied')->count();
                $first_position = Member::where('circle_id', $circle->id)->where('position', 1)->first();
                $second_position = Member::where('circle_id', $circle->id)->where('position', 2)->first();
                $third_position = Member::where('circle_id', $circle->id)->where('position', 3)->first();
                $fourth_position = Member::where('circle_id', $circle->id)->where('position', 4)->first();
                $fifth_position = Member::where('circle_id', $circle->id)->where('position', 5)->first();
                $six_position = Member::where('circle_id', $circle->id)->where('position', 6)->first();
                $seven_position = Member::where('circle_id', $circle->id)->where('position', 7)->first();
                $eight_position = Member::where('circle_id', $circle->id)->where('position', 8)->first();
                $nine_position = Member::where('circle_id', $circle->id)->where('position', 9)->first();
                $ten_position = Member::where('circle_id', $circle->id)->where('position', 10)->first();
                $eleven_position = Member::where('circle_id', $circle->id)->where('position', 11)->first();
                $twelve_position = Member::where('circle_id', $circle->id)->where('position', 12)->first();
                $thirteen_position = Member::where('circle_id', $circle->id)->where('position', 13)->first();
                $fourteen_position = Member::where('circle_id', $circle->id)->where('position', 14)->first();
                $fifteen_position = Member::where('circle_id', $circle->id)->where('position', 15)->first();
                $sixteen_position = Member::where('circle_id', $circle->id)->where('position', 16)->first();
                $seventeen_position = Member::where('circle_id', $circle->id)->where('position', 17)->first();
                $eighteen_position = Member::where('circle_id', $circle->id)->where('position', 18)->first();
                $nineteen_position = Member::where('circle_id', $circle->id)->where('position', 19)->first();
                $twenty_position = Member::where('circle_id', $circle->id)->where('position', 20)->first();
                $twenty_one_position = Member::where('circle_id', $circle->id)->where('position', 21)->first();
                        
                foreach($circle->members as $member){
                    if($jump_position == 7){
                        if($occupied_count == 1){
                            $third_position->user_id = $first_position->user_id;
                            $third_position->status = 'Occupied';
                            $third_position->save();
                            
                            $first_position->user_id = $user_id;
                            $first_position->status = 'Occupied';
                            $first_position->save();
                            break;
                        }
                        if($occupied_count == 2){
                            $seven_position->user_id = $third_position->user_id;
                            $seven_position->status = 'Occupied';
                            $seven_position->save();
                            
                            $third_position->user_id = $first_position->user_id;
                            $third_position->status = 'Occupied';
                            $third_position->save();
                            
                            $six_position->user_id = $user_id;
                            $six_position->status = 'Occupied';
                            $six_position->save();
                            
                            $first_position->user_id = null;
                            $first_position->status = 'Empty';
                            $first_position->save();
                            break;
                        }
                        if ($occupied_count > 2 && $occupied_count < 7) {
                            if($condition == 1){
                                if($third_position->user_id == $current_user->referal_id || $first_position->user_id == $current_user->referal_id){
                                    $this->assignPosition([$first_position, $second_position, $fifth_position,$fourth_position], $user_id);
                                    break;
                                }
                                if($six_position->user_id == $current_user->referal_id || $fifth_position->user_id == $current_user->referal_id){
                                    $this->assignPosition([$fifth_position,$fourth_position,$first_position, $second_position], $user_id);
                                    break;
                                }
                            }
                            $this->assignPosition([$first_position, $second_position, $fifth_position,$fourth_position], $user_id);
                            break;
                        }
                    }
                    
                    if($jump_position == 13){
                        if($occupied_count == 1){
                            $fourth_position->user_id = $first_position->user_id;
                            $fourth_position->status = 'Occupied';
                            $fourth_position->save();
                            
                            $first_position->user_id = $user_id;
                            $first_position->status = 'Occupied';
                            $first_position->save();
                            break;
                        }
                        if($occupied_count == 2){
                            $second_position->user_id = $user_id;
                            $second_position->status = 'Occupied';
                            $second_position->save();
                            break;
                        }
                        if($occupied_count == 3){
                                
                            $thirteen_position->user_id = $fourth_position->user_id;
                            $thirteen_position->status = 'Occupied';
                            $thirteen_position->save();
                            
                            $fourth_position->user_id = $first_position->user_id;
                            $fourth_position->status = 'Occupied';
                            $fourth_position->save();
                            
                            $eight_position->user_id = $second_position->user_id;
                            $eight_position->status = 'Occupied';
                            $eight_position->save();
                            
                            $twelve_position->user_id = $user_id;
                            $twelve_position->status = 'Occupied';
                            $twelve_position->save();
                            
                            $first_position->user_id = null;
                            $first_position->status = 'Empty';
                            $first_position->save();
                            
                            $second_position->user_id = null;
                            $second_position->status = 'Empty';
                            $second_position->save();
                            
                            $third_position->user_id = null;
                            $third_position->status = 'Empty';
                            $third_position->save();
                            
                            break;
                        }
                        if ($occupied_count > 3 && $occupied_count < 13) {
                            if($condition == 1){
                                if($fourth_position->user_id == $current_user->referal_id || $first_position->user_id == $current_user->referal_id 
                                    || $second_position->user_id == $current_user->referal_id){
                                    $this->assignPosition([$first_position, $second_position,$third_position,$fifth_position,$six_position,$seven_position,
                                    $eleven_position,$ten_position,$nine_position], $user_id);
                                    break;
                                }
                                if($eight_position->user_id == $current_user->referal_id || $fifth_position->user_id == $current_user->referal_id 
                                    || $six_position->user_id == $current_user->referal_id){
                                    $this->assignPosition([$fifth_position, $six_position,$seven_position,$first_position,$second_position,$third_position,
                                    $eleven_position,$ten_position,$nine_position], $user_id);
                                    break;
                                }
                                if($twelve_position->user_id == $current_user->referal_id || $eleven_position->user_id == $current_user->referal_id 
                                    || $ten_position->user_id == $current_user->referal_id){
                                    $this->assignPosition([$eleven_position,$ten_position,$nine_position,$first_position,$second_position,$third_position,
                                    $fifth_position, $six_position,$seven_position], $user_id);
                                    break;
                                }
                            }
                            $this->assignPosition([$first_position, $second_position, $third_position,$fifth_position, $six_position, $seven_position,
                                $eleven_position, $ten_position, $nine_position], $user_id);
                                break;
                        }
                    }
                    
                    if($jump_position == 21){
                        if($occupied_count == 1){
                            $fifth_position->user_id = $first_position->user_id;
                            $fifth_position->status = 'Occupied';
                            $fifth_position->save();
                            
                            $first_position->user_id = $user_id;
                            $first_position->status = 'Occupied';
                            $first_position->save();
                            break;
                        }
                        if($occupied_count == 2){
                            $second_position->user_id = $user_id;
                            $second_position->status = 'Occupied';
                            $second_position->save();
                            break;
                        }
                        if($occupied_count == 3){
                            $third_position->user_id = $user_id;
                            $third_position->status = 'Occupied';
                            $third_position->save();
                            break;
                        }
                        if($occupied_count == 4){
                            $twenty_one_position->user_id = $fifth_position->user_id;
                            $twenty_one_position->status = 'Occupied';
                            $twenty_one_position->save();
                            
                            $fifth_position->user_id = $first_position->user_id;
                            $fifth_position->status = 'Occupied';
                            $fifth_position->save();
                            
                            $ten_position->user_id = $second_position->user_id;
                            $ten_position->status = 'Occupied';
                            $ten_position->save();
                            
                            $fifteen_position->user_id = $user_id;
                            $fifteen_position->status = 'Occupied';
                            $fifteen_position->save();
                            
                            $twenty_position->user_id = $third_position->user_id;
                            $twenty_position->status = 'Occupied';
                            $twenty_position->save();
                            
                            $first_position->user_id = null;
                            $first_position->status = 'Empty';
                            $first_position->save();
                            
                            $second_position->user_id = null;
                            $second_position->status = 'Empty';
                            $second_position->save();
                            
                            $third_position->user_id = null;
                            $third_position->status = 'Empty';
                            $third_position->save();
                            
                            $fourth_position->user_id = null;
                            $fourth_position->status = 'Empty';
                            $fourth_position->save();
                            break;
                        }
                        
                        if ($occupied_count > 4 && $occupied_count < 21) {
                            if($condition == 1){
                                if($fifth_position->user_id == $current_user->referal_id || $first_position->user_id == $current_user->referal_id 
                                    || $second_position->user_id == $current_user->referal_id || $third_position->user_id == $current_user->referal_id){
                                    $this->assignPosition([$first_position, $second_position,$third_position,$fourth_position,$six_position,$seven_position,$eight_position,$nine_position,
                                    $nineteen_position,$eighteen_position,$seventeen_position,$sixteen_position,
                                    $fourteen_position,$thirteen_position,$twelve_position,$eleven_position], $user_id);
                                    break;
                                }
                                if($ten_position->user_id == $current_user->referal_id || $six_position->user_id == $current_user->referal_id 
                                    || $seven_position->user_id == $current_user->referal_id || $eight_position->user_id == $current_user->referal_id){
                                    $this->assignPosition([$six_position,$seven_position,$eight_position,$nine_position,$first_position, $second_position,$third_position,$fourth_position,
                                    $nineteen_position,$eighteen_position,$seventeen_position,$sixteen_position,
                                    $fourteen_position,$thirteen_position,$twelve_position,$eleven_position], $user_id);
                                    break;
                                }
                                if($fifteen_position->user_id == $current_user->referal_id || $fourteen_position->user_id == $current_user->referal_id 
                                    || $thirteen_position->user_id == $current_user->referal_id || $twelve_position->user_id == $current_user->referal_id){
                                    $this->assignPosition([$fourteen_position,$thirteen_position,$twelve_position,$eleven_position,$first_position, $second_position,$third_position,$fourth_position,
                                    $six_position,$seven_position,$eight_position,$nine_position,$nineteen_position,$eighteen_position,$seventeen_position,$sixteen_position,], $user_id);
                                    break;
                                }
                                if($twenty_position->user_id == $current_user->referal_id || $nineteen_position->user_id == $current_user->referal_id 
                                    || $eighteen_position->user_id == $current_user->referal_id || $seventeen_position->user_id == $current_user->referal_id){
                                    $this->assignPosition([$nineteen_position,$eighteen_position,$seventeen_position,$sixteen_position,$first_position, $second_position,$third_position,$fourth_position,
                                    $six_position,$seven_position,$eight_position,$nine_position,$fourteen_position,$thirteen_position,$twelve_position,$eleven_position], $user_id);
                                    break;
                                }
                            }
                            $this->assignPosition([$first_position, $second_position,$third_position,$fourth_position,$six_position,$seven_position,$eight_position,$nine_position,
                                $nineteen_position, $eighteen_position, $seventeen_position,$sixteen_position,$fourteen_position, $thirteen_position,$twelve_position,$eleven_position], $user_id);
                                break;
                        }
                    }
                }
        $this->is_section_completed($circle_id,$package_id,$user_id);
        $this->is_circle_completed($circle_id,$package_id,$user_id);
    }
    
    public function is_section_completed($circle_id,$package_id,$user_id)
    {
        
        $circle = Circle::find($circle_id);
        $current_user = User::find($user_id);
        $package = Package::find($package_id);
        if (!$package) {
            // Log::info('No Package Found');
        }
        $max_downlines = $package->max_downlines;
        switch ($max_downlines) {
            case 2:
            $jump_position = 7;
            break;
            case 3:
            $jump_position = 13;
            break;
            case 4:
            $jump_position = 21;
            break;
            default:
            return response()->json(['error' => 'Invalid max_downline value'], 400);
        }
        
        $occupied_count = Member::where('circle_id', $circle->id)->where('status', 'Occupied')->count();
                $first_position = Member::where('circle_id', $circle->id)->where('position', 1)->first();
                $second_position = Member::where('circle_id', $circle->id)->where('position', 2)->first();
                $third_position = Member::where('circle_id', $circle->id)->where('position', 3)->first();
                $fourth_position = Member::where('circle_id', $circle->id)->where('position', 4)->first();
                $fifth_position = Member::where('circle_id', $circle->id)->where('position', 5)->first();
                $six_position = Member::where('circle_id', $circle->id)->where('position', 6)->first();
                $seven_position = Member::where('circle_id', $circle->id)->where('position', 7)->first();
                $eight_position = Member::where('circle_id', $circle->id)->where('position', 8)->first();
                $nine_position = Member::where('circle_id', $circle->id)->where('position', 9)->first();
                $ten_position = Member::where('circle_id', $circle->id)->where('position', 10)->first();
                $eleven_position = Member::where('circle_id', $circle->id)->where('position', 11)->first();
                $twelve_position = Member::where('circle_id', $circle->id)->where('position', 12)->first();
                $thirteen_position = Member::where('circle_id', $circle->id)->where('position', 13)->first();
                $fourteen_position = Member::where('circle_id', $circle->id)->where('position', 14)->first();
                $fifteen_position = Member::where('circle_id', $circle->id)->where('position', 15)->first();
                $sixteen_position = Member::where('circle_id', $circle->id)->where('position', 16)->first();
                $seventeen_position = Member::where('circle_id', $circle->id)->where('position', 17)->first();
                $eighteen_position = Member::where('circle_id', $circle->id)->where('position', 18)->first();
                $nineteen_position = Member::where('circle_id', $circle->id)->where('position', 19)->first();
                $twenty_position = Member::where('circle_id', $circle->id)->where('position', 20)->first();
                $twenty_one_position = Member::where('circle_id', $circle->id)->where('position', 21)->first();
        
        if($jump_position == 7){
                    if($first_position->status == 'Occupied' && $second_position->status == 'Occupied' && $third_position->status == 'Occupied' && $seven_position->status == 'Occupied'){
                        $new_circle = Circle::where('user_id',$third_position->user_id)->where('package_id',$package->id)->where('status','Active')->first();
                        if(!$new_circle){
                            Log::info("creating new circle for ".$third_position->user->username);
                            $new_circle = new Circle();
                            $new_circle->user_id = $third_position->user_id;
                            $new_circle->name = $this->generateUniqueString(8);
                            $new_circle->package_id = $package->id;
                            $new_circle->reward_amount = 0;
                            $new_circle->status = 'Active';
                            $new_circle->save();
                            
                            for ($i = 1; $i <= $package->total_members; $i++){
                                $member = new Member();
                                $member->circle_id = $new_circle->id;
                                $member->position = $i;
                                if($i == 3){
                                    $member->user_id = $first_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 6){
                                    $member->user_id = $second_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 7){
                                    $member->user_id = $third_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                $member->package_id = $package->id;
                                $member->save();
                            }
                            
                        }
                        
                        // check if already given reward
                        Log::info("checking for reward - jump position 7");
                        $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',1)->first();
                        if(!$circle_reward){
                            Log::info("no reward given to ".$circle->user->username);
                            $downlines = $circle->user->downlines;
                            $purchsed_packages_count = 0;
                            foreach($downlines as $downline){
                                $downline_circle = Subscription::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                                if($downline_circle){
                                    $purchsed_packages_count = $purchsed_packages_count + 1;
                                }
                                if($purchsed_packages_count >= 2){
                                    break;
                                }
                            }
                            
                            if($purchsed_packages_count >= 2){
                                // add reward to referal
                                Log::info("creating trip for");
                                $trip = new Trip();
                                $trip->user_id = $circle->user_id;
                                $trip->save();
                                
                                Log::info("creating reward ".$circle->user->username);
                                $circle_reward = new CircleReward();
                                $circle_reward->user_id = $circle->user_id;
                                $circle_reward->circle_id = $circle->id;
                                $circle_reward->trip_id = $trip->id;
                                $circle_reward->amount = $package->reward_amount;
                                $circle_reward->section = 1;
                                $circle_reward->desc = '1st Section completed';
                                $circle_reward->status = 'Success';
                                $circle_reward->save();
                                
                                $timer = Timer::where('user_id',$circle->user_id)->where('package_id',$circle->package_id)->first();
                                $timer->started_at = now();
                                $timer->save();
                               // Add 10% bonus to reward amount
                                $base_reward = $package->reward_amount;
                                $bonus = ($base_reward * 10) / 100;
                                $total_reward = $base_reward + $bonus;

                                $circle->user->wallet = $circle->user->wallet + $total_reward;
                                $circle->user->save();

                                $this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '1st Section completed');
                            }else{
                                Log::info("reward not given due to downlines purchased count ".$purchsed_packages_count. " of ".$circle->user->username);
                            }
                        }
                    }
                    if($fourth_position->status == 'Occupied' && $fifth_position->status == 'Occupied' && $six_position->status == 'Occupied' && $seven_position->status == 'Occupied'){
                        $new_circle = Circle::where('user_id',$six_position->user_id)->where('package_id',$package->id)->where('status','Active')->first();
                        if(!$new_circle){
                                Log::info("creating new circle for ".$six_position->user->username);
                            $new_circle = new Circle();
                            $new_circle->user_id = $six_position->user_id;
                            $new_circle->name = $this->generateUniqueString(8);
                            $new_circle->package_id = $package->id;
                            $new_circle->reward_amount = 0;
                            $new_circle->status = 'Active';
                            $new_circle->save();
                            for ($i = 1; $i <= $package->total_members; $i++){
                                $member = new Member();
                                $member->circle_id = $new_circle->id;
                                $member->position = $i;
                                if($i == 3){
                                    $member->user_id = $fifth_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 6){
                                    $member->user_id = $fourth_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 7){
                                    $member->user_id = $six_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                $member->package_id = $package->id;
                                $member->save();
                            }
                            
                        }
                        
                        // check if already given reward
                        $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',2)->first();
                        if(!$circle_reward){
                                Log::info("no reward given to ".$circle->user->username);
                            $downlines = $circle->user->downlines;
                            $purchsed_packages_count = 0;
                            foreach($downlines as $downline){
                                $downline_circle = Subscription::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                                if($downline_circle){
                                    $purchsed_packages_count = $purchsed_packages_count + 1;
                                }
                                if($purchsed_packages_count >= 2){
                                    break;
                                }
                            }
                            
                            if($purchsed_packages_count >= 2){
                                // add reward to referal
                                    Log::info("creating trip for");
                                $trip = new Trip();
                                $trip->user_id = $circle->user_id;
                                $trip->save();
                                
                                    Log::info("creating reward ".$circle->user->username);
                                $circle_reward = new CircleReward();
                                $circle_reward->user_id = $circle->user_id;
                                $circle_reward->circle_id = $circle->id;
                                $circle_reward->trip_id = $trip->id;
                                $circle_reward->amount = $package->reward_amount;
                                $circle_reward->section = 2;
                                $circle_reward->desc = '2nd Section completed';
                                $circle_reward->status = 'Success';
                                $circle_reward->save();

                                $timer = Timer::where('user_id',$circle->user_id)->where('package_id',$circle->package_id)->first();
                                $timer->started_at = now();
                                $timer->save();
                                
                                // Add 10% bonus to reward amount
$base_reward = $package->reward_amount;
$bonus = ($base_reward * 10) / 100;
$total_reward = $base_reward + $bonus;

$circle->user->wallet = $circle->user->wallet + $total_reward;
$circle->user->save();

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '2nd Section completed');
                            }else{
                                Log::info("reward not given due to downlines purchased count ".$purchsed_packages_count. " of ".$circle->user->username);
                            }
                        }
                    }
                }
                    
        if($jump_position == 13){
                    // Log::info("checking for jump position 13");
                    if($first_position->status == 'Occupied' && $second_position->status == 'Occupied' && $third_position->status == 'Occupied' && $fourth_position->status == 'Occupied' && $thirteen_position->status == 'Occupied'){
                        $new_circle = Circle::where('user_id',$fourth_position->user_id)->where('status','Active')->where('package_id',$package->id)->first();
                        if(!$new_circle){
                            // Log::info("creating new circle for ".$fourth_position->user->username);
                            $new_circle = new Circle();
                            $new_circle->user_id = $fourth_position->user_id;
                            $new_circle->name = $this->generateUniqueString(8);
                            $new_circle->package_id = $package->id;
                            $new_circle->reward_amount = 0;
                            $new_circle->status = 'Active';
                            $new_circle->save();
                            
                            for ($i = 1; $i <= $package->total_members; $i++){
                                $member = new Member();
                                $member->circle_id = $new_circle->id;
                                $member->position = $i;
                                if($i == 4){
                                    $member->user_id = $first_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 8){
                                    $member->user_id = $second_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 12){
                                    $member->user_id = $third_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 13){
                                    $member->user_id = $fourth_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                $member->package_id = $package->id;
                                $member->save();
                            }
                            
                        }
                        
                        // check if already given reward
                        // Log::info("checking for reward - jump position 13");
                        $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',1)->first();
                        if(!$circle_reward){
                            // Log::info("no reward given to ".$circle->user->username);
                            $downlines = $circle->user->downlines;
                            $purchsed_packages_count = 0;
                            foreach($downlines as $downline){
                                $downline_circle = Subscription::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                                if($downline_circle){
                                    $purchsed_packages_count = $purchsed_packages_count + 1;
                                }
                                if($purchsed_packages_count >= 3){
                                    break;
                                }
                            }
                            
                            if($purchsed_packages_count >= 3){
                                // add reward to referal
                                // Log::info("creating trip for");
                                $trip = new Trip();
                                $trip->user_id = $circle->user_id;
                                $trip->save();
                                
                                // Log::info("creating reward ".$circle->user->username);
                                $circle_reward = new CircleReward();
                                $circle_reward->user_id = $circle->user_id;
                                $circle_reward->circle_id = $circle->id;
                                $circle_reward->trip_id = $trip->id;
                                $circle_reward->amount = $package->reward_amount;
                                $circle_reward->section = 1;
                                $circle_reward->desc = '1st Section completed';
                                $circle_reward->status = 'Success';
                                $circle_reward->save();

                                $timer = Timer::where('user_id',$circle->user_id)->where('package_id',$circle->package_id)->first();
                                $timer->started_at = now();
                                $timer->save();
                                
                                // Add 10% bonus to reward amount
$base_reward = $package->reward_amount;
$bonus = ($base_reward * 10) / 100;
$total_reward = $base_reward + $bonus;

$circle->user->wallet = $circle->user->wallet + $total_reward;
$circle->user->save();

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '1st Section completed');
                            }else{
                                // Log::info("reward not given due to downlines purchased count ".$purchsed_packages_count. " of ".$circle->user->username);
                            }
                        }
                    }
                    // 2nd section of 13th members circle
                    if($fifth_position->status == 'Occupied' && $six_position->status == 'Occupied' && $seven_position->status == 'Occupied' && $eight_position->status == 'Occupied' && $thirteen_position->status == 'Occupied'){
                        $new_circle = Circle::where('user_id',$eight_position->user_id)->where('status','Active')->where('package_id',$package->id)->first();
                        if(!$new_circle){
                            // Log::info("creating new circle for ".$eight_position->user->username);
                            $new_circle = new Circle();
                            $new_circle->user_id = $eight_position->user_id;
                            $new_circle->name = $this->generateUniqueString(8);
                            $new_circle->package_id = $package->id;
                            $new_circle->reward_amount = 0;
                            $new_circle->status = 'Active';
                            $new_circle->save();
                            
                            for ($i = 1; $i <= $package->total_members; $i++){
                                $member = new Member();
                                $member->circle_id = $new_circle->id;
                                $member->position = $i;
                                if($i == 4){
                                    $member->user_id = $fifth_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 8){
                                    $member->user_id = $six_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 12){
                                    $member->user_id = $seven_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 13){
                                    $member->user_id = $eight_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                $member->package_id = $package->id;
                                $member->save();
                            }
                            
                        }
                        
                        // check if already given reward
                        // Log::info("checking for reward - jump position 13");
                        $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',2)->first();
                        if(!$circle_reward){
                            // Log::info("no reward given to ".$circle->user->username);
                            $downlines = $circle->user->downlines;
                            $purchsed_packages_count = 0;
                            foreach($downlines as $downline){
                                $downline_circle = Subscription::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                                if($downline_circle){
                                    $purchsed_packages_count = $purchsed_packages_count + 1;
                                }
                                if($purchsed_packages_count >= 3){
                                    break;
                                }
                            }
                            
                            if($purchsed_packages_count >= 3){
                                // add reward to referal
                                // Log::info("creating trip for");
                                $trip = new Trip();
                                $trip->user_id = $circle->user_id;
                                $trip->save();
                                
                                // Log::info("creating reward ".$circle->user->username);
                                $circle_reward = new CircleReward();
                                $circle_reward->user_id = $circle->user_id;
                                $circle_reward->circle_id = $circle->id;
                                $circle_reward->trip_id = $trip->id;
                                $circle_reward->amount = $package->reward_amount;
                                $circle_reward->section = 2;
                                $circle_reward->desc = '2nd Section completed';
                                $circle_reward->status = 'Success';
                                $circle_reward->save();

                                $timer = Timer::where('user_id',$circle->user_id)->where('package_id',$circle->package_id)->first();
                                $timer->started_at = now();
                                $timer->save();
                                
                                // Add 10% bonus to reward amount
$base_reward = $package->reward_amount;
$bonus = ($base_reward * 10) / 100;
$total_reward = $base_reward + $bonus;

$circle->user->wallet = $circle->user->wallet + $total_reward;
$circle->user->save();

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '2nd Section completed');
                            }else{
                                // Log::info("reward not given due to downlines purchased count ".$purchsed_packages_count. " of ".$circle->user->username);
                            }
                        }
                    }
                    
                    // 3rd section of 13th members circle
                    if($nine_position->status == 'Occupied' && $ten_position->status == 'Occupied' && $eleven_position->status == 'Occupied' && $twelve_position->status == 'Occupied' && $thirteen_position->status == 'Occupied'){
                        $new_circle = Circle::where('user_id',$twelve_position->user_id)->where('status','Active')->where('package_id',$package->id)->first();
                        if(!$new_circle){
                            // Log::info("creating new circle for ".$twelve_position->user->username);
                            $new_circle = new Circle();
                            $new_circle->user_id = $twelve_position->user_id;
                            $new_circle->name = $this->generateUniqueString(8);
                            $new_circle->package_id = $package->id;
                            $new_circle->reward_amount = 0;
                            $new_circle->status = 'Active';
                            $new_circle->save();
                            
                            for ($i = 1; $i <= $package->total_members; $i++){
                                $member = new Member();
                                $member->circle_id = $new_circle->id;
                                $member->position = $i;
                                if($i == 4){
                                    $member->user_id = $eleven_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 8){
                                    $member->user_id = $ten_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 12){
                                    $member->user_id = $nine_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 13){
                                    $member->user_id = $twelve_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                $member->package_id = $package->id;
                                $member->save();
                            }
                            
                        }
                        
                        // check if already given reward
                        // Log::info("checking for reward - jump position 13");
                        $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',3)->first();
                        if(!$circle_reward){
                            // Log::info("no reward given to ".$circle->user->username);
                            $downlines = $circle->user->downlines;
                            $purchsed_packages_count = 0;
                            foreach($downlines as $downline){
                                $downline_circle = Subscription::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                                if($downline_circle){
                                    $purchsed_packages_count = $purchsed_packages_count + 1;
                                }
                                if($purchsed_packages_count >= 3){
                                    break;
                                }
                            }
                            
                            if($purchsed_packages_count >= 3){
                                // add reward to referal
                                // Log::info("creating trip for");
                                $trip = new Trip();
                                $trip->user_id = $circle->user_id;
                                $trip->save();
                                
                                // Log::info("creating reward ".$circle->user->username);
                                $circle_reward = new CircleReward();
                                $circle_reward->user_id = $circle->user_id;
                                $circle_reward->circle_id = $circle->id;
                                $circle_reward->trip_id = $trip->id;
                                $circle_reward->amount = $package->reward_amount;
                                $circle_reward->section = 3;
                                $circle_reward->desc = '3rd Section completed';
                                $circle_reward->status = 'Success';
                                $circle_reward->save();

                                $timer = Timer::where('user_id',$circle->user_id)->where('package_id',$circle->package_id)->first();
                                $timer->started_at = now();
                                $timer->save();
                                
                                // Add 10% bonus to reward amount
$base_reward = $package->reward_amount;
$bonus = ($base_reward * 10) / 100;
$total_reward = $base_reward + $bonus;

$circle->user->wallet = $circle->user->wallet + $total_reward;
$circle->user->save();

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '3rd Section completed');
                            }else{
                                // Log::info("reward not given due to downlines purchased count ".$purchsed_packages_count. " of ".$circle->user->username);
                            }
                        }
                    }
                }
                
        if($jump_position == 21){
                    // Log::info("checking for jump position 21");
                    if($first_position->status == 'Occupied' && $second_position->status == 'Occupied' && $third_position->status == 'Occupied' && $fourth_position->status == 'Occupied' 
                        && $fifth_position->status == 'Occupied' && $twenty_one_position->status == 'Occupied'){
                            
                        $new_circle = Circle::where('user_id',$fifth_position->user_id)->where('status','Active')->where('package_id',$package->id)->first();
                        if(!$new_circle){
                            // Log::info("creating new circle for ".$fifth_position->user->username);
                            $new_circle = new Circle();
                            $new_circle->user_id = $fifth_position->user_id;
                            $new_circle->name = $this->generateUniqueString(8);
                            $new_circle->package_id = $package->id;
                            $new_circle->reward_amount = 0;
                            $new_circle->status = 'Active';
                            $new_circle->save();
                            
                            for ($i = 1; $i <= $package->total_members; $i++){
                                $member = new Member();
                                $member->circle_id = $new_circle->id;
                                $member->position = $i;
                                if($i == 5){
                                    $member->user_id = $first_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 10){
                                    $member->user_id = $second_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 15){
                                    $member->user_id = $third_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 20){
                                    $member->user_id = $fourth_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 21){
                                    $member->user_id = $fifth_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                $member->package_id = $package->id;
                                $member->save();
                            }
                            
                        }
                        
                        // check if already given reward
                        // Log::info("checking for reward - jump position 21");
                        $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',1)->first();
                        if(!$circle_reward){
                            // Log::info("no reward given to ".$circle->user->username);
                            $downlines = $circle->user->downlines;
                            $purchsed_packages_count = 0;
                            foreach($downlines as $downline){
                                $downline_circle = Subscription::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                                if($downline_circle){
                                    $purchsed_packages_count = $purchsed_packages_count + 1;
                                }
                                if($purchsed_packages_count >= 4){
                                    break;
                                }
                            }
                            
                            if($purchsed_packages_count >= 4){
                                // add reward to referal
                                // Log::info("creating trip for");
                                $trip = new Trip();
                                $trip->user_id = $circle->user_id;
                                $trip->save();
                                
                                // Log::info("creating reward ".$circle->user->username);
                                $circle_reward = new CircleReward();
                                $circle_reward->user_id = $circle->user_id;
                                $circle_reward->circle_id = $circle->id;
                                $circle_reward->trip_id = $trip->id;
                                $circle_reward->amount = $package->reward_amount;
                                $circle_reward->section = 1;
                                $circle_reward->desc = '1st Section completed';
                                $circle_reward->status = 'Success';
                                $circle_reward->save();

                                $timer = Timer::where('user_id',$circle->user_id)->where('package_id',$circle->package_id)->first();
                                $timer->started_at = now();
                                $timer->save();
                                
                                // Add 10% bonus to reward amount
$base_reward = $package->reward_amount;
$bonus = ($base_reward * 10) / 100;
$total_reward = $base_reward + $bonus;

$circle->user->wallet = $circle->user->wallet + $total_reward;
$circle->user->save();

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '1st Section completed');
                            }else{
                                // Log::info("reward not given due to downlines purchased count ".$purchsed_packages_count. " of ".$circle->user->username);
                            }
                        }
                    }
                    // 2nd section of 21th members circle
                    if($six_position->status == 'Occupied' && $seven_position->status == 'Occupied' && $eight_position->status == 'Occupied' && $nine_position->status == 'Occupied' 
                        && $ten_position->status == 'Occupied' && $twenty_one_position->status == 'Occupied'){
                        $new_circle = Circle::where('user_id',$ten_position->user_id)->where('status','Active')->where('package_id',$package->id)->first();
                        if(!$new_circle){
                            // Log::info("creating new circle for ".$ten_position->user->username);
                            $new_circle = new Circle();
                            $new_circle->user_id = $ten_position->user_id;
                            $new_circle->name = $this->generateUniqueString(8);
                            $new_circle->package_id = $package->id;
                            $new_circle->reward_amount = 0;
                            $new_circle->status = 'Active';
                            $new_circle->save();
                            
                            for ($i = 1; $i <= $package->total_members; $i++){
                                $member = new Member();
                                $member->circle_id = $new_circle->id;
                                $member->position = $i;
                                if($i == 5){
                                    $member->user_id = $six_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 10){
                                    $member->user_id = $seven_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 15){
                                    $member->user_id = $eight_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 20){
                                    $member->user_id = $nine_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 21){
                                    $member->user_id = $ten_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                $member->package_id = $package->id;
                                $member->save();
                            }
                            
                        }
                        
                        // check if already given reward
                        // Log::info("checking for reward - jump position 21");
                        $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',2)->first();
                        if(!$circle_reward){
                            // Log::info("no reward given to ".$circle->user->username);
                            $downlines = $circle->user->downlines;
                            $purchsed_packages_count = 0;
                            foreach($downlines as $downline){
                                $downline_circle = Subscription::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                                if($downline_circle){
                                    $purchsed_packages_count = $purchsed_packages_count + 1;
                                }
                                if($purchsed_packages_count >= 4){
                                    break;
                                }
                            }
                            
                            if($purchsed_packages_count >= 4){
                                // add reward to referal
                                // Log::info("creating trip for");
                                $trip = new Trip();
                                $trip->user_id = $circle->user_id;
                                $trip->save();
                                
                                // Log::info("creating reward ".$circle->user->username);
                                $circle_reward = new CircleReward();
                                $circle_reward->user_id = $circle->user_id;
                                $circle_reward->circle_id = $circle->id;
                                $circle_reward->trip_id = $trip->id;
                                $circle_reward->amount = $package->reward_amount;
                                $circle_reward->section = 2;
                                $circle_reward->desc = '2nd Section completed';
                                $circle_reward->status = 'Success';
                                $circle_reward->save();
                                
                                $timer = Timer::where('user_id',$circle->user_id)->where('package_id',$circle->package_id)->first();
                                $timer->started_at = now();
                                $timer->save();

                                // Add 10% bonus to reward amount
$base_reward = $package->reward_amount;
$bonus = ($base_reward * 10) / 100;
$total_reward = $base_reward + $bonus;

$circle->user->wallet = $circle->user->wallet + $total_reward;
$circle->user->save();

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '2nd Section completed');
                            }else{
                                // Log::info("reward not given due to downlines purchased count ".$purchsed_packages_count. " of ".$circle->user->username);
                            }
                        }
                    }
                    
                    // 3rd section of 21th members circle
                    if($eleven_position->status == 'Occupied' && $twelve_position->status == 'Occupied' && $thirteen_position->status == 'Occupied' 
                        && $fourteen_position->status == 'Occupied' && $fifteen_position->status == 'Occupied' && $twenty_one_position->status == 'Occupied'){
                        $new_circle = Circle::where('user_id',$fifteen_position->user_id)->where('status','Active')->where('package_id',$package->id)->first();
                        if(!$new_circle){
                            // Log::info("creating new circle for ".$fifteen_position->user->username);
                            $new_circle = new Circle();
                            $new_circle->user_id = $fifteen_position->user_id;
                            $new_circle->name = $this->generateUniqueString(8);
                            $new_circle->package_id = $package->id;
                            $new_circle->reward_amount = 0;
                            $new_circle->status = 'Active';
                            $new_circle->save();
                            
                            for ($i = 1; $i <= $package->total_members; $i++){
                                $member = new Member();
                                $member->circle_id = $new_circle->id;
                                $member->position = $i;
                                if($i == 5){
                                    $member->user_id = $fourteen_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 10){
                                    $member->user_id = $thirteen_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 15){
                                    $member->user_id = $twelve_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 20){
                                    $member->user_id = $eleven_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 21){
                                    $member->user_id = $fifteen_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                $member->package_id = $package->id;
                                $member->save();
                            }
                            
                        }
                        
                        // check if already given reward
                        // Log::info("checking for reward - jump position 21");
                        $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',3)->first();
                        if(!$circle_reward){
                            // Log::info("no reward given to ".$circle->user->username);
                            $downlines = $circle->user->downlines;
                            $purchsed_packages_count = 0;
                            foreach($downlines as $downline){
                                $downline_circle = Subscription::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                                if($downline_circle){
                                    $purchsed_packages_count = $purchsed_packages_count + 1;
                                }
                                if($purchsed_packages_count >= 4){
                                    break;
                                }
                            }
                            
                            if($purchsed_packages_count >= 4){
                                // add reward to referal
                                // Log::info("creating trip for");
                                $trip = new Trip();
                                $trip->user_id = $circle->user_id;
                                $trip->save();
                                
                                // Log::info("creating reward ".$circle->user->username);
                                $circle_reward = new CircleReward();
                                $circle_reward->user_id = $circle->user_id;
                                $circle_reward->circle_id = $circle->id;
                                $circle_reward->trip_id = $trip->id;
                                $circle_reward->amount = $package->reward_amount;
                                $circle_reward->section = 3;
                                $circle_reward->desc = '3rd Section completed';
                                $circle_reward->status = 'Success';
                                $circle_reward->save();

                                $timer = Timer::where('user_id',$circle->user_id)->where('package_id',$circle->package_id)->first();
                                $timer->started_at = now();
                                $timer->save();
                                
                                // Add 10% bonus to reward amount
$base_reward = $package->reward_amount;
$bonus = ($base_reward * 10) / 100;
$total_reward = $base_reward + $bonus;

$circle->user->wallet = $circle->user->wallet + $total_reward;
$circle->user->save();

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '3rd Section completed');
                            }else{
                                // Log::info("reward not given due to downlines purchased count ".$purchsed_packages_count. " of ".$circle->user->username);
                            }
                        }
                    }
                    
                    // 4th section of 21th members circle
                    if($sixteen_position->status == 'Occupied' && $seventeen_position->status == 'Occupied' && $eighteen_position->status == 'Occupied' 
                        && $nineteen_position->status == 'Occupied' && $twenty_position->status == 'Occupied' && $twenty_one_position->status == 'Occupied'){
                        $new_circle = Circle::where('user_id',$twenty_position->user_id)->where('status','Active')->where('package_id',$package->id)->first();
                        if(!$new_circle){
                            // Log::info("creating new circle for ".$twenty_position->user->username);
                            $new_circle = new Circle();
                            $new_circle->user_id = $twenty_position->user_id;
                            $new_circle->name = $this->generateUniqueString(8);
                            $new_circle->package_id = $package->id;
                            $new_circle->reward_amount = 0;
                            $new_circle->status = 'Active';
                            $new_circle->save();
                            
                            for ($i = 1; $i <= $package->total_members; $i++){
                                $member = new Member();
                                $member->circle_id = $new_circle->id;
                                $member->position = $i;
                                if($i == 5){
                                    $member->user_id = $nineteen_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 10){
                                    $member->user_id = $eighteen_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 15){
                                    $member->user_id = $seventeen_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 20){
                                    $member->user_id = $sixteen_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                if($i == 21){
                                    $member->user_id = $twenty_position->user_id;
                                    $member->status = 'Occupied';
                                }
                                $member->package_id = $package->id;
                                $member->save();
                            }
                            
                        }
                        
                        // check if already given reward
                        // Log::info("checking for reward - jump position 21");
                        $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',4)->first();
                        if(!$circle_reward){
                            // Log::info("no reward given to ".$circle->user->username);
                            $downlines = $circle->user->downlines;
                            $purchsed_packages_count = 0;
                            foreach($downlines as $downline){
                                $downline_circle = Subscription::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                                if($downline_circle){
                                    $purchsed_packages_count = $purchsed_packages_count + 1;
                                }
                                if($purchsed_packages_count >= 4){
                                    break;
                                }
                            }
                            
                            if($purchsed_packages_count >= 4){
                                // add reward to referal
                                // Log::info("creating trip for");
                                $trip = new Trip();
                                $trip->user_id = $circle->user_id;
                                $trip->save();
                                
                                // Log::info("creating reward ".$circle->user->username);
                                $circle_reward = new CircleReward();
                                $circle_reward->user_id = $circle->user_id;
                                $circle_reward->circle_id = $circle->id;
                                $circle_reward->trip_id = $trip->id;
                                $circle_reward->amount = $package->reward_amount;
                                $circle_reward->section = 4;
                                $circle_reward->desc = '4th Section completed';
                                $circle_reward->status = 'Success';
                                $circle_reward->save();

                                $timer = Timer::where('user_id',$circle->user_id)->where('package_id',$circle->package_id)->first();
                                $timer->started_at = now();
                                $timer->save();
                                
                                // Add 10% bonus to reward amount
$base_reward = $package->reward_amount;
$bonus = ($base_reward * 10) / 100;
$total_reward = $base_reward + $bonus;

$circle->user->wallet = $circle->user->wallet + $total_reward;
$circle->user->save();

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '4th Section completed');
                            }else{
                                // Log::info("reward not given due to downlines purchased count ".$purchsed_packages_count. " of ".$circle->user->username);
                            }
                        }
                    }
                }
    }
    
    public function is_circle_completed($circle_id,$package_id,$user_id)
    {
        $circle = Circle::find($circle_id);
        $current_user = User::find($user_id);
        $package = Package::find($package_id);
        if (!$package) {
            Log::info('No Package Found');
        }
        
        // Skip 5-member circles - they have their own completion logic
        if($package->total_members == 5){
            return; // 5-member circles are handled by check_5_member_circle_completion
        }
        
        $max_downlines = $package->max_downlines;
        switch ($max_downlines) {
            case 2:
            $jump_position = 7;
            break;
            case 3:
            $jump_position = 13;
            break;
            case 4:
            $jump_position = 21;
            break;
            default:
            return response()->json(['error' => 'Invalid max_downline value'], 400);
        }
        $occupied_count = Member::where('circle_id', $circle->id)->where('status', 'Occupied')->count();
                if ($occupied_count == $package->total_members) {
                    $circle->status = 'Completed';
                    $circle->save();

                    $circle->user->wallet = $circle->user->wallet - $package->reward_amount;
                    $circle->user->save();
                    $this->create_transaction($circle->user_id, 'Debit', $package->reward_amount, $circle->user->wallet, $package->name.' Package Purchased');

                    Log::info("check for uplines after circle complete");

                    // Check upline circle
                    $upline_circle = $this->findUplineCircle($circle->user, $package->id);
                    if ($upline_circle) {
                        Log::info($circle->user->username . " goes to upline - " . $upline_circle->user->username . " circle after completing");
                        $this->fill_directly($upline_circle->id,$package_id,$circle->user_id);
                        return;
                    }

                    Log::info("check for downlines after circle complete");


                    // Check downlines
                    foreach ($circle->user->downlines as $downline) {
                        Log::info($circle->user->username . " goes to downline - " . $downline->username . " circle after completing");

                        $downline_circle = Circle::with('package')->where('package_id', $package->id)
                            ->where('user_id', $downline->id)
                            ->where('status', 'Active')
                            ->first();

                        if ($downline_circle) {
                            Log::info("downline circle found -" . $downline->username);
                            $this->fill_directly($downline_circle->id,$package_id,$circle->user_id);
                            return true;
                        }
                    }

                    Log::info("check for upline -> downlines after circle complete");

                    // Check upline -> downlines circles
                    $upline = $circle->user->referal;
                    if ($upline) {
                        foreach ($upline->downlines as $downline) {
                            $downline_circle = Circle::with('package')->where('package_id', $package->id)
                                ->where('user_id', $downline->id)
                                ->where('status', 'Active')
                                ->first();

                            if ($downline_circle) {
                                Log::info("circle found -" . $downline->username);
                                $this->fill_directly($downline_circle->id,$package_id,$circle->user_id);
                                return true;
                            }
                        }
                    }
                }

    }
    
    private function findUplineCircle($user, $package_id)
    {
        if($user->id == 1){
            return null;
        }
        while ($user->referal) {
            Log::info("searching upline for" . $user->username);
            $upline = $user->referal;
            if(!$upline){
                return null;
            }
            if($upline){
                Log::info("upline found" . $upline->username);
                $upline_circle = Circle::with('package')->where('user_id', $upline->id)
                    ->where('package_id', $package_id)
                    ->where('status', 'Active')
                    ->first();
        
                if ($upline_circle) {
                    Log::info("circle found -" . $upline_circle->id);
                    return $upline_circle;
                }else{
                    $member = Member::where('user_id',$upline->id)->where('package_id',$package_id)->orderBy('id','desc')->first();
                    if($member){
                        $circle = $member->circle;
                        return $circle;
                    }
                    
                }
                // Move to the next upline
                 $user = $upline;
            }
        }
    
        return null; // No eligible upline circle found
    }

    
    private function create_transaction($user_id, $type, $amount, $balance, $reason){
        $transaction = new Transaction();
        $transaction->user_id = $user_id;
        $transaction->type = $type;
        $transaction->amount = $amount;
        $transaction->balance = $balance;
        $transaction->reason = $reason;
        $transaction->save();
    }
    
    private function assignPosition($positions, $user_id)
    {
        foreach ($positions as $position) {
            if ($position->status == 'Empty') {
                $position->user_id = $user_id;
                $position->status = 'Occupied';
                $position->save();
                return;
            }
        }
    }
    
    private function generateUniqueString($length = 8)
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Handle 5-member circle logic (Simple Circle)
     * User goes directly to position 5 after package purchase
     * Only direct referrals of the user (position 5) fill positions 1-4
     * IMPORTANT: Every user gets their OWN circle - no upliners/downliners involved
     */
    private function handle_5_member_circle($user, $package_id)
    {
        $package = Package::find($package_id);
        
        // Step 1: ALWAYS create or get user's own 5-member circle and place user in position 5
        // Every user gets their own circle - this is the key difference from other circle types
        $existing_circle = Circle::where('user_id', $user->id)
            ->where('package_id', $package_id)
            ->where('status', 'Active')
            ->first();
        
        if(!$existing_circle){
            // Create new circle for this user
            $user->create_5_member_circle($package_id);
            $existing_circle = Circle::where('user_id', $user->id)
                ->where('package_id', $package_id)
                ->where('status', 'Active')
                ->orderBy('id', 'desc')
                ->first();
        }
        
        // Place user in position 5 of their own circle
        $position_5 = Member::where('circle_id', $existing_circle->id)
            ->where('position', 5)
            ->first();
        
        if($position_5 && $position_5->status == 'Empty'){
            $position_5->user_id = $user->id;
            $position_5->status = 'Occupied';
            $position_5->save();
        }
        
        // Step 2: If this user is a direct referral of someone with a 5-member circle,
        // fill positions 1-4 of that upline's circle (but user still has their own circle)
        if($user->referal_id){
            $upline = User::find($user->referal_id);
            if($upline){
                // Check if upline has an active 5-member circle for this package
                $upline_circle = Circle::where('user_id', $upline->id)
                    ->where('package_id', $package_id)
                    ->where('status', 'Active')
                    ->first();
                
                if($upline_circle){
                    // Check if position 5 is occupied by upline
                    $position_5_upline = Member::where('circle_id', $upline_circle->id)
                        ->where('position', 5)
                        ->where('user_id', $upline->id)
                        ->where('status', 'Occupied')
                        ->first();
                    
                    if($position_5_upline){
                        // Upline has a 5-member circle, fill positions 1-4 of upline's circle
                        // This user will also have their own circle (created above)
                        $this->fill_5_member_circle($upline_circle->id, $package_id, $user->id);
                    }
                }
            }
        }
        
        // Step 3: Check if user's existing direct referrals can fill positions 1-4 of this user's circle
        // This handles the case where user already had referrals before purchasing
        $this->fill_existing_direct_referrals_5_member_circle($existing_circle->id, $package_id, $user->id);
    }
    
    /**
     * Fill 5-member circle with existing direct referrals
     * Called after circle owner purchases to fill with existing referrals
     */
    private function fill_existing_direct_referrals_5_member_circle($circle_id, $package_id, $circle_owner_id)
    {
        $circle = Circle::find($circle_id);
        $circle_owner = User::find($circle_owner_id);
        $package = Package::find($package_id);
        
        if(!$circle || !$circle_owner || !$package){
            return;
        }
        
        // Get all direct referrals of the circle owner who have purchased this package
        $direct_referrals = User::where('referal_id', $circle_owner_id)
            ->whereHas('subscriptions', function($query) use ($package_id) {
                $query->where('subscriptions.package_id', $package_id)
                      ->where('subscriptions.status', 'Active');
            })
            ->get();
        
        // Get empty positions 1-4
        $empty_positions = Member::where('circle_id', $circle_id)
            ->whereIn('position', [1, 2, 3, 4])
            ->where('status', 'Empty')
            ->orderBy('position', 'asc')
            ->get();
        
        // Fill positions with direct referrals
        $position_index = 0;
        foreach($direct_referrals as $referral){
            // Check if referral is already in this circle
            $existing_member = Member::where('circle_id', $circle_id)
                ->where('user_id', $referral->id)
                ->first();
            
            if($existing_member){
                continue; // Skip if already in circle
            }
            
            if($position_index < count($empty_positions)){
                $position = $empty_positions[$position_index];
                $position->user_id = $referral->id;
                $position->status = 'Occupied';
                $position->save();
                
                // Give reward after each position is filled
                $this->give_5_member_circle_reward($circle_id, $package_id, $position->position);
                
                $position_index++;
            }
        }
        
        // Check if circle is completed
        $this->check_5_member_circle_completion($circle_id, $package_id);
    }
    
    /**
     * Fill 5-member circle with direct referrals only
     * Only users directly referred by the circle owner (position 5) can fill positions 1-4
     * This is called when a direct referral purchases the package
     */
    private function fill_5_member_circle($circle_id, $package_id, $purchasing_user_id)
    {
        $circle = Circle::find($circle_id);
        $circle_owner = User::find($circle->user_id); // User in position 5
        $package = Package::find($package_id);
        $purchasing_user = User::find($purchasing_user_id);
        
        if(!$circle || !$circle_owner || !$package || !$purchasing_user){
            return;
        }
        
        // Verify that purchasing user is a direct referral of circle owner
        if($purchasing_user->referal_id != $circle_owner->id){
            return; // Not a direct referral, don't fill
        }
        
        // Verify that purchasing user has active subscription for this package
        $subscription = Subscription::where('user_id', $purchasing_user_id)
            ->where('package_id', $package_id)
            ->where('status', 'Active')
            ->first();
        
        if(!$subscription){
            return; // User doesn't have active subscription
        }
        
        // Check if this user is already in a position in this circle
        $existing_member = Member::where('circle_id', $circle_id)
            ->where('user_id', $purchasing_user_id)
            ->first();
        
        if($existing_member){
            return; // User already in this circle
        }
        
        // Get first empty position (1-4)
        $empty_position = Member::where('circle_id', $circle_id)
            ->whereIn('position', [1, 2, 3, 4])
            ->where('status', 'Empty')
            ->orderBy('position', 'asc')
            ->first();
        
        if($empty_position){
            // Fill the position
            $empty_position->user_id = $purchasing_user_id;
            $empty_position->status = 'Occupied';
            $empty_position->save();
            
            // Give reward after each position is filled
            $this->give_5_member_circle_reward($circle_id, $package_id, $empty_position->position);
            
            // Check if circle is completed
            $this->check_5_member_circle_completion($circle_id, $package_id);
        }
    }
    
    /**
     * Give reward after each position is filled in 5-member circle
     */
    private function give_5_member_circle_reward($circle_id, $package_id, $position)
    {
        $circle = Circle::find($circle_id);
        $package = Package::find($package_id);
        
        if(!$circle || !$package){
            return;
        }
        
        $circle_owner = $circle->user; // User in position 5
        
        // Calculate reward: Full reward_amount + 10% bonus for each position filled
        $base_reward = $package->reward_amount; // Full reward amount (e.g., 200)
        
        // Add 10% bonus on top of base reward
        $bonus = ($base_reward * 10) / 100; // 10% bonus (e.g., 20)
        $final_reward = $base_reward + $bonus; // Total reward (e.g., 200 + 20 = 220)
        
        // Add to not_withdraw_amount (5-member circle rewards are not withdrawable)
        $circle_owner->not_withdraw_amount = ($circle_owner->not_withdraw_amount ?? 0) + $final_reward;
        $circle_owner->save();
        
        // Calculate total balance (wallet + not_withdraw_amount) for transaction record
        $total_balance = ($circle_owner->wallet ?? 0) + ($circle_owner->not_withdraw_amount ?? 0);
        
        // Create transaction (show normally, but amount is in not_withdraw_amount)
        // Simple description without breakdown details
        $this->create_transaction(
            $circle_owner->id, 
            'Credit', 
            $final_reward, 
            $total_balance, 
            "5-Member Circle Position {$position} filled"
        );
        
        // Create circle reward record
        $circle_reward = new CircleReward();
        $circle_reward->user_id = $circle_owner->id;
        $circle_reward->circle_id = $circle_id;
        $circle_reward->amount = $base_reward; // Store base reward
        $circle_reward->section = $position;
        $circle_reward->desc = "Position {$position} filled in 5-Member Circle";
        $circle_reward->status = 'Success';
        $circle_reward->save();
    }
    
    /**
     * Check if 5-member circle is completed
     * If completed, mark as completed and show message (don't renew)
     */
    private function check_5_member_circle_completion($circle_id, $package_id)
    {
        $circle = Circle::find($circle_id);
        $package = Package::find($package_id);
        
        if(!$circle || !$package){
            return;
        }
        
        $occupied_count = Member::where('circle_id', $circle_id)
            ->where('status', 'Occupied')
            ->count();
        
        if($occupied_count == 5){
            // Circle is completed
            $circle->status = 'Completed';
            $circle->save();
            
            // Don't renew - just mark as completed
            // The message will be shown in the frontend when checking circle status
            Log::info("5-Member Circle {$circle_id} completed for user {$circle->user_id}. No renewal.");
        }
    }

    /**
     * Handle combo package logic (2x 5-member + 1x 21-member)
     */
    private function handle_combo_package($user, $package_id)
    {
        $package = Package::find($package_id);
        if(!$package || !$package->is_combo){
            return;
        }

        // 5-member circles - each user gets their own (same as before)
        $five_a = $this->get_or_create_combo_circle($user, $package_id, 'five_a');
        $five_b = $this->get_or_create_combo_circle($user, $package_id, 'five_b');
        $five_c = $this->get_or_create_combo_circle($user, $package_id, 'five_c');

        $this->ensure_combo_circle_center($five_a, $user->id);
        $this->ensure_combo_circle_center($five_b, $user->id);
        $this->ensure_combo_circle_center($five_c, $user->id);

        // Direct referral fills for the three 5-member sections
        if($user->referal_id){
            $upline_five_a = ComboCircle::where('package_id', $package_id)
                ->where('section', 'five_a')
                ->where('user_id', $user->referal_id)
                ->where('status', 'Active')
                ->first();
            if($upline_five_a){
                $this->combo_fill_five_direct_referral($upline_five_a->id, $package_id, $user->id);
            }

            $upline_five_b = ComboCircle::where('package_id', $package_id)
                ->where('section', 'five_b')
                ->where('user_id', $user->referal_id)
                ->where('status', 'Active')
                ->first();
            if($upline_five_b){
                $this->combo_fill_five_direct_referral($upline_five_b->id, $package_id, $user->id);
            }

            $upline_five_c = ComboCircle::where('package_id', $package_id)
                ->where('section', 'five_c')
                ->where('user_id', $user->referal_id)
                ->where('status', 'Active')
                ->first();
            if($upline_five_c){
                $this->combo_fill_five_direct_referral($upline_five_c->id, $package_id, $user->id);
            }
        }

        // Fill any existing direct referrals into user's new 5-member circles
        $this->combo_fill_existing_direct_referrals_five($five_a->id, $package_id, $user->id);
        $this->combo_fill_existing_direct_referrals_five($five_b->id, $package_id, $user->id);
        $this->combo_fill_existing_direct_referrals_five($five_c->id, $package_id, $user->id);

        // 21-member circle logic - EXACTLY like regular 21-member circle (users fill into upline's circle)
        // Check if user's direct upline has a combo 21-member circle
        if($user->referal_id){
            // First check if direct upline owns a 21-member combo circle
            $upline_circle = ComboCircle::where('package_id',$package_id)
                ->where('section', 'twentyone')
                ->where('user_id', $user->referal_id)
                ->where('status','Active')
                ->first();

            if($upline_circle){
                // Direct upline has an active 21-member circle - fill into it
                $this->combo_fill_twentyone($upline_circle->id, $package_id, $user->id);
            }else{
                // Direct upline doesn't have an active circle - search up the chain
                $circle = $this->combo_find_upline_circle($user, $package_id);
                if($circle){
                    $this->combo_fill_twentyone_directly($circle->id, $package_id, $user->id);
                }else{
                    // No upline circle found - create own (only if no upline has one)
                    $user->create_combo_circle($package_id, 'twentyone', 1, true);
                }
            }
        }else{
            // User has no referal (admin) - find upline circle or create own
            $circle = $this->combo_find_upline_circle($user, $package_id);
            if($circle){
                $this->combo_fill_twentyone_directly($circle->id, $package_id, $user->id);
            }else{
                // No upline circle exists - create own circle
                $user->create_combo_circle($package_id, 'twentyone', 1, true);
            }
        }

        // Autofill only starts after first circles complete in both 5-member sections
        $this->combo_autofill_for_new_member($user, $package_id);
    }

    private function get_or_create_combo_circle($user, $package_id, $section)
    {
        $circle = ComboCircle::where('user_id', $user->id)
            ->where('package_id', $package_id)
            ->where('section', $section)
            ->where('status', 'Active')
            ->orderBy('id', 'desc')
            ->first();

        if(!$circle){
            $completed_count = ComboCircle::where('user_id', $user->id)
                ->where('package_id', $package_id)
                ->where('section', $section)
                ->where('status', 'Completed')
                ->count();
            $cycle = $completed_count + 1;
            $circle = $user->create_combo_circle($package_id, $section, $cycle);
        }

        return $circle;
    }

    private function ensure_combo_circle_center($circle, $user_id)
    {
        if(!$circle){
            return;
        }
        $position = $circle->section === 'twentyone' ? 1 : 5;
        $member = ComboMember::where('combo_circle_id', $circle->id)
            ->where('position', $position)
            ->first();
        if($member && $member->status == 'Empty'){
            $member->user_id = $user_id;
            $member->status = 'Occupied';
            $member->save();
        }
    }

    private function combo_fill_existing_direct_referrals_five($circle_id, $package_id, $circle_owner_id)
    {
        $circle = ComboCircle::find($circle_id);
        $circle_owner = User::find($circle_owner_id);
        $package = Package::find($package_id);

        if(!$circle || !$circle_owner || !$package){
            return;
        }

        $direct_referrals = User::where('referal_id', $circle_owner_id)
            ->whereHas('subscriptions', function($query) use ($package_id) {
                $query->where('subscriptions.package_id', $package_id)
                      ->where('subscriptions.status', 'Active');
            })
            ->get();

        $empty_positions = ComboMember::where('combo_circle_id', $circle_id)
            ->whereIn('position', [1, 2, 3, 4])
            ->where('status', 'Empty')
            ->orderBy('position', 'asc')
            ->get();

        $position_index = 0;
        foreach($direct_referrals as $referral){
            $existing_member = ComboMember::where('combo_circle_id', $circle_id)
                ->where('user_id', $referral->id)
                ->first();

            if($existing_member){
                continue;
            }

            if($position_index < count($empty_positions)){
                $position = $empty_positions[$position_index];
                $position->user_id = $referral->id;
                $position->status = 'Occupied';
                $position->placement_type = 'direct';
                $position->save();

                $this->combo_give_five_reward($circle_id, $package_id, $position->position, 'direct');
                $position_index++;
            }
        }

        $this->combo_check_five_circle_completion($circle_id, $package_id);
    }

    private function combo_fill_five_direct_referral($circle_id, $package_id, $purchasing_user_id)
    {
        $circle = ComboCircle::find($circle_id);
        $circle_owner = User::find($circle->user_id);
        $package = Package::find($package_id);
        $purchasing_user = User::find($purchasing_user_id);

        if(!$circle || !$circle_owner || !$package || !$purchasing_user){
            return;
        }

        if($purchasing_user->referal_id != $circle_owner->id){
            return;
        }

        $subscription = Subscription::where('user_id', $purchasing_user_id)
            ->where('package_id', $package_id)
            ->where('status', 'Active')
            ->first();

        if(!$subscription){
            return;
        }

        $existing_member = ComboMember::where('combo_circle_id', $circle_id)
            ->where('user_id', $purchasing_user_id)
            ->first();

        if($existing_member){
            return;
        }

        $empty_position = ComboMember::where('combo_circle_id', $circle_id)
            ->whereIn('position', [1, 2, 3, 4])
            ->where('status', 'Empty')
            ->orderBy('position', 'asc')
            ->first();

        if($empty_position){
            $empty_position->user_id = $purchasing_user_id;
            $empty_position->status = 'Occupied';
            $empty_position->placement_type = 'direct';
            $empty_position->save();

            $this->combo_give_five_reward($circle_id, $package_id, $empty_position->position, 'direct');
            $this->combo_check_five_circle_completion($circle_id, $package_id);
        }
    }

    private function combo_fill_five_autofill($circle_id, $package_id, $purchasing_user_id)
    {
        $circle = ComboCircle::find($circle_id);
        $package = Package::find($package_id);
        $purchasing_user = User::find($purchasing_user_id);

        if(!$circle || !$package || !$purchasing_user){
            return;
        }

        $existing_member = ComboMember::where('combo_circle_id', $circle_id)
            ->where('user_id', $purchasing_user_id)
            ->first();

        if($existing_member){
            return;
        }

        $empty_position = ComboMember::where('combo_circle_id', $circle_id)
            ->whereIn('position', [1, 2, 3, 4])
            ->where('status', 'Empty')
            ->orderBy('position', 'asc')
            ->first();

        if($empty_position){
            $empty_position->user_id = $purchasing_user_id;
            $empty_position->status = 'Occupied';
            $empty_position->placement_type = 'autofill';
            $empty_position->save();

            $this->combo_give_five_reward($circle_id, $package_id, $empty_position->position, 'autofill');
            $this->combo_check_five_circle_completion($circle_id, $package_id);
        }
    }

    private function combo_give_five_reward($circle_id, $package_id, $position, $placement_type)
    {
        $circle = ComboCircle::find($circle_id);
        $package = Package::find($package_id);
        $circle_owner = $circle ? User::find($circle->user_id) : null;

        if(!$circle || !$package || !$circle_owner){
            return;
        }

        $reward = 0;
        // Use the appropriate reward based on the section (five_a, five_b, or five_c)
        if($circle->section === 'five_a'){
            if($placement_type === 'direct'){
                $reward = $package->combo_five_a_reward_direct;
            } else {
                $reward = $package->combo_five_a_reward_autofill;
            }
        } else if($circle->section === 'five_b'){
            if($placement_type === 'direct'){
                $reward = $package->combo_five_b_reward_direct;
            } else {
                $reward = $package->combo_five_b_reward_autofill;
            }
        } else if($circle->section === 'five_c'){
            if($placement_type === 'direct'){
                $reward = $package->combo_five_c_reward_direct;
            } else {
                $reward = $package->combo_five_c_reward_autofill;
            }
        }

        $circle_owner->combo_wallet = ($circle_owner->combo_wallet ?? 0) + $reward;
        $circle_owner->save();

        $this->create_transaction(
            $circle_owner->id,
            'Credit',
            $reward,
            $circle_owner->combo_wallet,
            "Combo 5-Member ({$circle->section}) Position {$position} filled"
        );

        $circle_reward = new ComboCircleReward();
        $circle_reward->user_id = $circle_owner->id;
        $circle_reward->combo_circle_id = $circle->id;
        $circle_reward->amount = $reward;
        $circle_reward->section = $circle->section;
        $circle_reward->desc = "Position {$position} filled in Combo 5-Member {$circle->section}";
        $circle_reward->status = 'Success';
        $circle_reward->save();
    }

    private function combo_check_five_circle_completion($circle_id, $package_id)
    {
        $circle = ComboCircle::find($circle_id);
        $package = Package::find($package_id);

        if(!$circle || !$package){
            return;
        }

        $occupied_count = ComboMember::where('combo_circle_id', $circle_id)
            ->where('status', 'Occupied')
            ->count();

        if($occupied_count == 5){
            $circle->status = 'Completed';
            $circle->save();

            $owner = User::find($circle->user_id);
            if($owner){
                // Use the appropriate auto-renew amount based on the section (five_a, five_b, or five_c)
                if($circle->section === 'five_a'){
                    $autorenew_amount = $package->combo_five_a_autorenew_amount;
                } else if($circle->section === 'five_b'){
                    $autorenew_amount = $package->combo_five_b_autorenew_amount;
                } else if($circle->section === 'five_c'){
                    $autorenew_amount = $package->combo_five_c_autorenew_amount;
                } else {
                    $autorenew_amount = 0;
                }
                $this->combo_debit_autorenew($owner, $autorenew_amount, "Combo 5-Member {$circle->section} Auto-Renew");
                $new_circle = $owner->create_combo_circle($package_id, $circle->section, $circle->cycle + 1);
                $this->ensure_combo_circle_center($new_circle, $owner->id);
                // After renewal: fill 4 positions (upliner/downliners one layer) and place owner into upliner's or downliner's circles
                $this->combo_autofill_after_renewal($owner, $package_id, $new_circle);
            }
        }
    }

    private function combo_is_autofill_eligible_user($user_id, $package_id)
    {
        /**
         * AUTOFILL ELIGIBILITY RULE:
         * A user is eligible for autofill ONLY if they have completed their FIRST circle
         * in ALL THREE sections (five_a, five_b, and five_c) with 4 DIRECT REFERRALS.
         *
         * This means:
         * - The user's cycle=1 circle in five_a must be Completed with all 4 positions filled by DIRECT referrals
         * - The user's cycle=1 circle in five_b must be Completed with all 4 positions filled by DIRECT referrals
         * - The user's cycle=1 circle in five_c must be Completed with all 4 positions filled by DIRECT referrals
         *
         * Users who joined via autofill do NOT make the circle owner eligible.
         * Only direct referrals count toward eligibility.
         */

        // Check five_a section - first circle (cycle=1) must be completed with 4 direct referrals
        $first_circle_five_a = ComboCircle::where('user_id', $user_id)
            ->where('package_id', $package_id)
            ->where('section', 'five_a')
            ->where('cycle', 1) // First circle only
            ->where('status', 'Completed')
            ->first();

        if (!$first_circle_five_a) {
            return false; // First circle not completed
        }

        // Check if all 4 positions (1-4) were filled by DIRECT referrals
        $direct_count_five_a = ComboMember::where('combo_circle_id', $first_circle_five_a->id)
            ->whereIn('position', [1, 2, 3, 4])
            ->where('status', 'Occupied')
            ->where('placement_type', 'direct') // Only count direct referrals
            ->count();

        if ($direct_count_five_a < 4) {
            return false; // Not all 4 positions were filled by direct referrals
        }

        // Check five_b section - first circle (cycle=1) must be completed with 4 direct referrals
        $first_circle_five_b = ComboCircle::where('user_id', $user_id)
            ->where('package_id', $package_id)
            ->where('section', 'five_b')
            ->where('cycle', 1) // First circle only
            ->where('status', 'Completed')
            ->first();

        if (!$first_circle_five_b) {
            return false; // First circle not completed
        }

        // Check if all 4 positions (1-4) were filled by DIRECT referrals
        $direct_count_five_b = ComboMember::where('combo_circle_id', $first_circle_five_b->id)
            ->whereIn('position', [1, 2, 3, 4])
            ->where('status', 'Occupied')
            ->where('placement_type', 'direct') // Only count direct referrals
            ->count();

        if ($direct_count_five_b < 4) {
            return false; // Not all 4 positions were filled by direct referrals
        }

        // Check five_c section - first circle (cycle=1) must be completed with 4 direct referrals
        $first_circle_five_c = ComboCircle::where('user_id', $user_id)
            ->where('package_id', $package_id)
            ->where('section', 'five_c')
            ->where('cycle', 1) // First circle only
            ->where('status', 'Completed')
            ->first();

        if (!$first_circle_five_c) {
            return false; // First circle not completed
        }

        // Check if all 4 positions (1-4) were filled by DIRECT referrals
        $direct_count_five_c = ComboMember::where('combo_circle_id', $first_circle_five_c->id)
            ->whereIn('position', [1, 2, 3, 4])
            ->where('status', 'Occupied')
            ->where('placement_type', 'direct') // Only count direct referrals
            ->count();

        if ($direct_count_five_c < 4) {
            return false; // Not all 4 positions were filled by direct referrals
        }

        return true; // User is eligible for autofill
    }

    private function combo_autofill_for_new_member($user, $package_id)
    {
        /**
         * COMBO PACKAGE NEW MEMBER AUTOFILL LOGIC (Per requirements 2.4):
         *
         * IMPORTANT: Autofill ONLY happens AFTER the user has completed their OWN first circle
         * with 4 DIRECT referrals in ALL THREE sections (five_a, five_b, AND five_c).
         *
         * PRIORITY ORDER:
         * 1. Check UPLINER FIRST (direct upliner only, one layer)
         * 2. If upliner not eligible, check DOWNLINERS with round-robin rotation
         *
         * Round-robin logic for downliners:
         * - Track last autofill index for fair rotation among downliners
         * - If last autofill was to downliner index 0, next goes to index 1
         * - After all downliners, wrap back to index 0
         *
         * If the purchasing user is NOT eligible (hasn't completed first circles), NO autofill happens.
         * They can only join circles via DIRECT REFERRAL until they complete their first circles.
         */

        // FIRST: Check if the purchasing user is eligible for autofill
        if (!$this->combo_is_autofill_eligible_user($user->id, $package_id)) {
            return;
        }

        // Step 1: Check UPLINER FIRST (direct upliner only)
        if ($user->referal_id) {
            $direct_upline = User::find($user->referal_id);

            if ($direct_upline && $this->combo_is_autofill_eligible_user($direct_upline->id, $package_id)) {
                $filled = false;

                // Fill into upline's circles (all three sections)
                // Note: We check for active circles directly instead of subscription
                // because admin (package creator) may not have subscription but has circles
                foreach (['five_a', 'five_b', 'five_c'] as $section) {
                    $upline_circle = ComboCircle::where('package_id', $package_id)
                        ->where('section', $section)
                        ->where('user_id', $direct_upline->id)
                        ->where('status', 'Active')
                        ->first();

                    if ($upline_circle) {
                        // Check if there's an empty position and user not already in circle
                        $has_empty = ComboMember::where('combo_circle_id', $upline_circle->id)
                            ->whereIn('position', [1, 2, 3, 4])
                            ->where('status', 'Empty')
                            ->exists();
                        $already_in = ComboMember::where('combo_circle_id', $upline_circle->id)
                            ->where('user_id', $user->id)
                            ->exists();

                        if ($has_empty && !$already_in) {
                            $this->combo_fill_five_autofill($upline_circle->id, $package_id, $user->id);
                            $filled = true;
                        }
                    }
                }

                if ($filled) {
                    return; // Successfully filled into upliner's circles, exit
                }
            }
        }

        // Step 2: If upliner not eligible, check downliners with round-robin
        $direct_downlines = User::where('referal_id', $user->id)
            ->whereHas('subscriptions', function($q) use ($package_id) {
                $q->where('subscriptions.package_id', $package_id)
                  ->where('subscriptions.status', 'Active');
            })
            ->orderBy('id', 'asc') // Consistent ordering for round-robin
            ->get();

        if ($direct_downlines->isEmpty()) {
            return; // No downliners to check
        }

        // Filter to only eligible downliners
        $eligible_downlines = [];
        foreach ($direct_downlines as $downline) {
            if ($this->combo_is_autofill_eligible_user($downline->id, $package_id)) {
                $eligible_downlines[] = $downline;
            }
        }

        if (empty($eligible_downlines)) {
            return; // No eligible downliners
        }

        // Round-robin: Get the next index based on last_combo_autofill_index
        $total_eligible = count($eligible_downlines);
        $last_index = $user->last_combo_autofill_index ?? 0;

        // Calculate next index (wrap around if needed)
        $next_index = ($last_index + 1) % $total_eligible;

        // Get the target downliner at next_index
        $target = $eligible_downlines[$next_index];

        // Fill user into target downliner's circles (all three sections)
        foreach (['five_a', 'five_b', 'five_c'] as $section) {
            $target_circle = ComboCircle::where('package_id', $package_id)
                ->where('section', $section)
                ->where('user_id', $target->id)
                ->where('status', 'Active')
                ->first();

            if ($target_circle) {
                $has_empty = ComboMember::where('combo_circle_id', $target_circle->id)
                    ->whereIn('position', [1, 2, 3, 4])
                    ->where('status', 'Empty')
                    ->exists();
                $already_in = ComboMember::where('combo_circle_id', $target_circle->id)
                    ->where('user_id', $user->id)
                    ->exists();

                if ($has_empty && !$already_in) {
                    $this->combo_fill_five_autofill($target_circle->id, $package_id, $user->id);
                }
            }
        }

        // Update user's last_combo_autofill_index
        $user->last_combo_autofill_index = $next_index;
        $user->save();
    }

    private function combo_autofill_after_renewal($owner, $package_id, $new_circle)
    {
        /**
         * COMBO PACKAGE AUTO-RENEWAL AUTOFILL LOGIC:
         *
         * After a 5-member circle completes and renews:
         *
         * CRITICAL RULES:
         * 1. Fill ONLY ONE position after auto-renewal
         * 2. Check UPLINER FIRST (direct upliner only, one layer)
         * 3. If upliner not eligible, check DOWNLINERS with round-robin rotation
         * 4. Fill owner into the SAME section circle as the renewed circle
         * 5. Track last autofill index for fair rotation among downliners
         *
         * Round-robin logic for downliners:
         * - If last autofill was to downliner index 0, next goes to index 1
         * - After index 3 (4th downliner), wrap back to index 0
         * - This ensures fair distribution among all eligible downliners
         *
         * Eligibility:
         * - Target (upliner/downliner) must be eligible to receive the owner
         * - Target must have active subscription and active circle with empty position
         */

        // Get the section of the renewed circle (five_a, five_b, or five_c)
        $section = $new_circle->section;

        // Ensure owner has active subscription (required to be placed into other circles)
        $owner_subscription = \App\Models\Subscription::where('user_id', $owner->id)
            ->where('package_id', $package_id)
            ->where('status', 'Active')
            ->first();

        if (!$owner_subscription) {
            return; // Owner doesn't have active subscription, can't be placed
        }

        // Step 1: Check UPLINER FIRST (direct upliner only, one layer)
        if ($owner->referal_id) {
            $direct_upline = User::find($owner->referal_id);

            if ($direct_upline && $this->combo_is_autofill_eligible_user($direct_upline->id, $package_id)) {
                // Find upline's active circle in the same section
                // Note: We check for active circles directly instead of subscription
                // because admin (package creator) may not have subscription but has circles
                $upline_circle = ComboCircle::where('package_id', $package_id)
                    ->where('section', $section)
                    ->where('user_id', $direct_upline->id)
                    ->where('status', 'Active')
                    ->first();

                if ($upline_circle) {
                    // Check if there's an empty position
                    $has_empty = ComboMember::where('combo_circle_id', $upline_circle->id)
                        ->whereIn('position', [1, 2, 3, 4])
                        ->where('status', 'Empty')
                        ->exists();

                    // Check if owner is already in this circle
                    $already_in = ComboMember::where('combo_circle_id', $upline_circle->id)
                        ->where('user_id', $owner->id)
                        ->exists();

                    if ($has_empty && !$already_in) {
                        // Fill owner into upline's circle
                        $this->combo_fill_five_autofill($upline_circle->id, $package_id, $owner->id);
                        return; // Successfully filled, exit
                    }
                }
            }
        }

        // Step 2: If upliner not eligible or no space, check downliners with round-robin
        $direct_downlines = User::where('referal_id', $owner->id)
            ->whereHas('subscriptions', function($q) use ($package_id) {
                $q->where('subscriptions.package_id', $package_id)
                  ->where('subscriptions.status', 'Active');
            })
            ->orderBy('id', 'asc') // Consistent ordering for round-robin
            ->get();

        if ($direct_downlines->isEmpty()) {
            return; // No downliners to check
        }

        // Filter to only eligible downliners with active circles that have empty positions
        $eligible_downlines = [];
        foreach ($direct_downlines as $downline) {
            if ($this->combo_is_autofill_eligible_user($downline->id, $package_id)) {
                $downline_circle = ComboCircle::where('package_id', $package_id)
                    ->where('section', $section)
                    ->where('user_id', $downline->id)
                    ->where('status', 'Active')
                    ->first();

                if ($downline_circle) {
                    // Check if there's an empty position
                    $has_empty = ComboMember::where('combo_circle_id', $downline_circle->id)
                        ->whereIn('position', [1, 2, 3, 4])
                        ->where('status', 'Empty')
                        ->exists();

                    // Check if owner is already in this circle
                    $already_in = ComboMember::where('combo_circle_id', $downline_circle->id)
                        ->where('user_id', $owner->id)
                        ->exists();

                    if ($has_empty && !$already_in) {
                        $eligible_downlines[] = [
                            'user' => $downline,
                            'circle' => $downline_circle
                        ];
                    }
                }
            }
        }

        if (empty($eligible_downlines)) {
            return; // No eligible downliners with available positions
        }

        // Round-robin: Get the next index based on last_combo_autofill_index
        $total_eligible = count($eligible_downlines);
        $last_index = $owner->last_combo_autofill_index ?? 0;

        // Calculate next index (wrap around if needed)
        $next_index = ($last_index + 1) % $total_eligible;

        // Get the target downliner at next_index
        $target = $eligible_downlines[$next_index];

        // Fill owner into target downliner's circle
        $this->combo_fill_five_autofill($target['circle']->id, $package_id, $owner->id);

        // Update owner's last_combo_autofill_index
        $owner->last_combo_autofill_index = $next_index;
        $owner->save();
    }

    private function combo_fill_user_into_circle($circle, $package_id, $filling_user_id)
    {
        // Fill filling_user into the specific circle
        // Return true if filled, false if not
        // Don't fill user into their own circle
        if($circle->user_id == $filling_user_id){
            return false;
        }
        
        $existing_member = ComboMember::where('combo_circle_id', $circle->id)
            ->where('user_id', $filling_user_id)
            ->first();

        if($existing_member){
            return false; // Already in circle
        }

        $empty_position = ComboMember::where('combo_circle_id', $circle->id)
            ->whereIn('position', [1, 2, 3, 4])
            ->where('status', 'Empty')
            ->orderBy('position', 'asc')
            ->first();

        if($empty_position){
            $empty_position->user_id = $filling_user_id;
            $empty_position->status = 'Occupied';
            $empty_position->placement_type = 'autofill';
            $empty_position->save();

            $this->combo_give_five_reward($circle->id, $package_id, $empty_position->position, 'autofill');
            $this->combo_check_five_circle_completion($circle->id, $package_id);
            return true;
        }
        
        return false;
    }

    private function combo_fill_user_into_circles($target_user, $package_id, $filling_user_id, $max_count)
    {
        // Fill filling_user into target_user's circles (all three 5-member sections)
        // Return number of positions filled
        $filled = 0;
        
        // Don't fill user into their own circles
        if($target_user->id == $filling_user_id){
            return 0;
        }
        
        $circle_a = ComboCircle::where('package_id', $package_id)
            ->where('section', 'five_a')
            ->where('user_id', $target_user->id)
            ->where('status', 'Active')
            ->first();
        if($circle_a){
            $empty_count = ComboMember::where('combo_circle_id', $circle_a->id)
                ->whereIn('position', [1, 2, 3, 4])
                ->where('status', 'Empty')
                ->count();
            if($empty_count > 0){
                $this->combo_fill_five_autofill($circle_a->id, $package_id, $filling_user_id);
                $filled++;
            }
        }

        if($filled < $max_count){
            $circle_b = ComboCircle::where('package_id', $package_id)
                ->where('section', 'five_b')
                ->where('user_id', $target_user->id)
                ->where('status', 'Active')
                ->first();
            if($circle_b){
                $empty_count = ComboMember::where('combo_circle_id', $circle_b->id)
                    ->whereIn('position', [1, 2, 3, 4])
                    ->where('status', 'Empty')
                    ->count();
                if($empty_count > 0){
                    $this->combo_fill_five_autofill($circle_b->id, $package_id, $filling_user_id);
                    $filled++;
                }
            }
        }

        if($filled < $max_count){
            $circle_c = ComboCircle::where('package_id', $package_id)
                ->where('section', 'five_c')
                ->where('user_id', $target_user->id)
                ->where('status', 'Active')
                ->first();
            if($circle_c){
                $empty_count = ComboMember::where('combo_circle_id', $circle_c->id)
                    ->whereIn('position', [1, 2, 3, 4])
                    ->where('status', 'Empty')
                    ->count();
                if($empty_count > 0){
                    $this->combo_fill_five_autofill($circle_c->id, $package_id, $filling_user_id);
                    $filled++;
                }
            }
        }
        
        return $filled;
    }

    private function combo_find_autofill_targets($user, $package_id)
    {
        /**
         * COMBO PACKAGE AUTOFILL PRIORITY LOGIC:
         * Per requirements 2.4 (i): Give first chance to upliners, then downliners.
         *
         * Search order:
         * 1. Search UPLINE levels (direct upline first, then upline's upline, etc.) until top
         * 2. If NO eligible upline found, search DOWNLINE levels (direct downlines first, then their downlines, etc.)
         * 3. Return ALL eligible users found (one level at a time - either upliners OR downliners, not both)
         *
         * Eligibility: User must have completed their OWN 1st circle in BOTH five_a AND five_b sections
         */

        // Admin (id=1) has no upline, so skip upline search for admin
        if ($user->id != 1 && $user->referal_id) {
            // Step 1: Search through ALL upline levels for eligible users
            $eligible_uplines = [];
            $current_upline = User::find($user->referal_id);

            while ($current_upline) {
                if ($current_upline->id != $user->id && $this->combo_is_autofill_eligible_user($current_upline->id, $package_id)) {
                    $eligible_uplines[] = $current_upline;
                }
                // Move to next level upline
                $current_upline = $current_upline->referal;
            }

            // If found eligible upliners, return them (upliners get priority)
            if (count($eligible_uplines) > 0) {
                return $eligible_uplines;
            }
        }

        // Step 2: If no eligible upliners found (or user is admin), search through ALL downline levels
        $eligible_downlines = [];
        $queue = [];

        // Start with direct downlines
        $direct_downlines = User::where('referal_id', $user->id)
            ->whereHas('subscriptions', function($q) use ($package_id) {
                $q->where('subscriptions.package_id', $package_id)->where('subscriptions.status', 'Active');
            })
            ->get();

        foreach ($direct_downlines as $d) {
            $queue[] = $d;
        }

        // BFS through all downline levels
        while (count($queue) > 0) {
            $downline = array_shift($queue);

            if ($downline->id != $user->id && $this->combo_is_autofill_eligible_user($downline->id, $package_id)) {
                $eligible_downlines[] = $downline;
            }

            // Add this downline's downlines to queue (next level)
            $next_level = User::where('referal_id', $downline->id)
                ->whereHas('subscriptions', function($q) use ($package_id) {
                    $q->where('subscriptions.package_id', $package_id)->where('subscriptions.status', 'Active');
                })
                ->get();

            foreach ($next_level as $child) {
                $queue[] = $child;
            }
        }

        return $eligible_downlines;
    }

    private function combo_get_eligible_uplines($user, $package_id)
    {
        $eligible = [];
        $current = $user->referal;
        while($current){
            if($this->combo_is_autofill_eligible_user($current->id, $package_id)){
                $eligible[] = $current;
            }
            $current = $current->referal;
        }
        return $eligible;
    }

    private function combo_get_eligible_downlines($user, $package_id)
    {
        $eligible = [];
        $queue = $user->downlines ? $user->downlines->all() : [];

        while(count($queue) > 0){
            $downline = array_shift($queue);
            if($this->combo_is_autofill_eligible_user($downline->id, $package_id)){
                $eligible[] = $downline;
            }
            foreach($downline->downlines as $child){
                $queue[] = $child;
            }
        }

        return $eligible;
    }

    private function combo_debit_autorenew($user, $amount, $reason)
    {
        $user->combo_wallet = ($user->combo_wallet ?? 0) - $amount;
        $user->save();

        $this->create_transaction(
            $user->id,
            'Debit',
            $amount,
            $user->combo_wallet,
            $reason
        );
    }

    /**
     * Calculate the locked amount in combo wallet for auto-renewal.
     *
     * For each active 5-member circle (five_a, five_b, five_c), the auto-renewal
     * amount is locked and cannot be withdrawn.
     *
     * @param int $user_id
     * @return float Total locked amount for auto-renewal
     */
    private function combo_get_locked_autorenew_amount($user_id)
    {
        $locked_amount = 0;

        // Get all combo packages
        $combo_packages = Package::where('is_combo', true)->get();

        foreach ($combo_packages as $package) {
            // Check for active five_a circle
            $active_five_a = ComboCircle::where('user_id', $user_id)
                ->where('package_id', $package->id)
                ->where('section', 'five_a')
                ->where('status', 'Active')
                ->exists();
            if ($active_five_a) {
                $locked_amount += $package->combo_five_a_autorenew_amount ?? 0;
            }

            // Check for active five_b circle
            $active_five_b = ComboCircle::where('user_id', $user_id)
                ->where('package_id', $package->id)
                ->where('section', 'five_b')
                ->where('status', 'Active')
                ->exists();
            if ($active_five_b) {
                $locked_amount += $package->combo_five_b_autorenew_amount ?? 0;
            }

            // Check for active five_c circle
            $active_five_c = ComboCircle::where('user_id', $user_id)
                ->where('package_id', $package->id)
                ->where('section', 'five_c')
                ->where('status', 'Active')
                ->exists();
            if ($active_five_c) {
                $locked_amount += $package->combo_five_c_autorenew_amount ?? 0;
            }
        }

        return $locked_amount;
    }

    private function combo_find_upline_circle($user, $package_id)
    {
        if($user->id == 1){
            return null;
        }
        while ($user->referal) {
            $upline = $user->referal;
            if(!$upline){
                return null;
            }
            // Check if upline owns a combo 21-member circle (same as regular circles)
            $upline_circle = ComboCircle::where('package_id', $package_id)
                ->where('section', 'twentyone')
                ->where('user_id', $upline->id)
                ->where('status', 'Active')
                ->first();
            
            if ($upline_circle) {
                return $upline_circle;
            }else{
                // Check if upline is a member of a combo 21-member circle (same as regular circles check Member)
                $combo_member = ComboMember::where('user_id',$upline->id)
                    ->where('package_id',$package_id)
                    ->whereHas('circle', function($query) {
                        $query->where('section', 'twentyone')
                              ->where('status', 'Active');
                    })
                    ->orderBy('id','desc')
                    ->first();
                if($combo_member){
                    $circle = $combo_member->circle;
                    if($circle && $circle->section == 'twentyone' && $circle->status == 'Active'){
                        return $circle;
                    }
                }
            }
            // Move to the next upline
            $user = $upline;
        }
        return null;
    }

    private function combo_fill_twentyone($circle_id, $package_id, $user_id)
    {
        $circle = ComboCircle::find($circle_id);
        $current_user = User::find($user_id);
        $package = Package::find($package_id);
        if (!$package) {
            return;
        }

        // Get the circle owner (referal) - this is the user who owns this circle
        $referal = User::find($circle->user_id);

        $occupied_count = ComboMember::where('combo_circle_id', $circle->id)->where('status', 'Occupied')->count();
        $first_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 1)->first();
        $second_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 2)->first();
        $third_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 3)->first();
        $fourth_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 4)->first();
        $fifth_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 5)->first();
        $six_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 6)->first();
        $seven_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 7)->first();
        $eight_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 8)->first();
        $nine_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 9)->first();
        $ten_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 10)->first();
        $eleven_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 11)->first();
        $twelve_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 12)->first();
        $thirteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 13)->first();
        $fourteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 14)->first();
        $fifteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 15)->first();
        $sixteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 16)->first();
        $seventeen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 17)->first();
        $eighteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 18)->first();
        $nineteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 19)->first();
        $twenty_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 20)->first();
        $twenty_one_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 21)->first();

        // EXACT match with original User.php 21-member logic (lines 820-895)
        // When occupied_count == 1: First downliner joins
        // New user → position 1, Owner → position 5
        if ($occupied_count == 1) {
            $first_position->user_id = $user_id;
            $first_position->status = 'Occupied';
            $first_position->save();

            $fifth_position->user_id = $referal->id;
            $fifth_position->status = 'Occupied';
            $fifth_position->save();
        }

        // When occupied_count == 2: Second downliner joins → position 2
        if ($occupied_count == 2) {
            $second_position->user_id = $user_id;
            $second_position->status = 'Occupied';
            $second_position->save();
        }

        // When occupied_count == 3: Third downliner joins → position 3
        if ($occupied_count == 3) {
            $third_position->user_id = $user_id;
            $third_position->status = 'Occupied';
            $third_position->save();
        }

        // When occupied_count == 4: Fourth downliner joins - MAJOR REARRANGEMENT
        // Owner → position 21 (center)
        // Position 1 user (user1) → position 5
        // Position 2 user (user2) → position 10
        // Position 3 user (user3) → position 15
        // New user (user4 - 4th downliner) → position 20
        // Clear positions 1,2,3,4
        if ($occupied_count == 4) {
            // Owner → position 21
            $twenty_one_position->user_id = $referal->id;
            $twenty_one_position->status = 'Occupied';
            $twenty_one_position->save();

            // Position 1 user (user1) → position 5
            $fifth_position->user_id = $first_position->user_id;
            $fifth_position->status = 'Occupied';
            $fifth_position->save();

            // Position 2 user (user2) → position 10
            $ten_position->user_id = $second_position->user_id;
            $ten_position->status = 'Occupied';
            $ten_position->save();

            // Position 3 user (user3) → position 15
            $fifteen_position->user_id = $third_position->user_id;
            $fifteen_position->status = 'Occupied';
            $fifteen_position->save();

            // New user (user4 - 4th downliner) → position 20
            $twenty_position->user_id = $user_id;
            $twenty_position->status = 'Occupied';
            $twenty_position->save();

            // Clear positions 1,2,3,4
            $first_position->user_id = null;
            $first_position->status = 'Empty';
            $first_position->save();

            $second_position->user_id = null;
            $second_position->status = 'Empty';
            $second_position->save();

            $third_position->user_id = null;
            $third_position->status = 'Empty';
            $third_position->save();

            $fourth_position->user_id = null;
            $fourth_position->status = 'Empty';
            $fourth_position->save();
        }

        // When occupied_count > 4 and < 21: Fill remaining positions based on referral section
        // Check section heads first (same as User.php fill()), then also check sub-positions (same as fill_directly())
        if ($occupied_count > 4 && $occupied_count < 21) {
            // Section 4: referrer is at position 20, 19, 18, or 17
            if ($current_user->referal_id == $twenty_position->user_id || $current_user->referal_id == $nineteen_position->user_id
                || $current_user->referal_id == $eighteen_position->user_id || $current_user->referal_id == $seventeen_position->user_id) {
                $this->combo_assign_position([$nineteen_position, $eighteen_position, $seventeen_position, $sixteen_position, $fourteen_position, $thirteen_position, $twelve_position, $eleven_position,
                    $first_position, $second_position, $third_position, $fourth_position, $six_position, $seven_position, $eight_position, $nine_position], $user_id);
            // Section 3: referrer is at position 15, 14, 13, or 12
            } elseif ($current_user->referal_id == $fifteen_position->user_id || $current_user->referal_id == $fourteen_position->user_id
                || $current_user->referal_id == $thirteen_position->user_id || $current_user->referal_id == $twelve_position->user_id) {
                $this->combo_assign_position([$fourteen_position, $thirteen_position, $twelve_position, $eleven_position, $nineteen_position, $eighteen_position, $seventeen_position, $sixteen_position,
                    $first_position, $second_position, $third_position, $fourth_position, $six_position, $seven_position, $eight_position, $nine_position], $user_id);
            // Section 2: referrer is at position 10, 6, 7, or 8
            } elseif ($current_user->referal_id == $ten_position->user_id || $current_user->referal_id == $six_position->user_id
                || $current_user->referal_id == $seven_position->user_id || $current_user->referal_id == $eight_position->user_id) {
                $this->combo_assign_position([$six_position, $seven_position, $eight_position, $nine_position, $first_position, $second_position, $third_position, $fourth_position,
                    $nineteen_position, $eighteen_position, $seventeen_position, $sixteen_position, $fourteen_position, $thirteen_position, $twelve_position, $eleven_position], $user_id);
            // Section 1: referrer is at position 5, 1, 2, or 3
            } elseif ($current_user->referal_id == $fifth_position->user_id || $current_user->referal_id == $first_position->user_id
                || $current_user->referal_id == $second_position->user_id || $current_user->referal_id == $third_position->user_id) {
                $this->combo_assign_position([$first_position, $second_position, $third_position, $fourth_position, $six_position, $seven_position, $eight_position, $nine_position,
                    $nineteen_position, $eighteen_position, $seventeen_position, $sixteen_position, $fourteen_position, $thirteen_position, $twelve_position, $eleven_position], $user_id);
            } else {
                // FALLBACK: Place in first available position
                $this->combo_assign_position([$first_position, $second_position, $third_position, $fourth_position, $six_position, $seven_position, $eight_position, $nine_position,
                    $nineteen_position, $eighteen_position, $seventeen_position, $sixteen_position, $fourteen_position, $thirteen_position, $twelve_position, $eleven_position], $user_id);
            }
        }

        $this->combo_is_section_completed_twentyone($circle_id, $package_id, $user_id);
        $this->combo_is_circle_completed_twentyone($circle_id, $package_id, $user_id);
    }

    /**
     * Find which section the user's referral chain belongs to in a 21-member circle
     * Searches up the referral chain to find someone who is in this circle
     */
    private function combo_find_referral_section_twentyone($user, $circle_id, $package_id)
    {
        $circle = ComboCircle::find($circle_id);
        if (!$circle) return null;

        // Get all positions
        $positions = [];
        for ($i = 1; $i <= 21; $i++) {
            $positions[$i] = ComboMember::where('combo_circle_id', $circle_id)->where('position', $i)->first();
        }

        // Walk up the referral chain to find someone in this circle
        $current = $user->referal;
        while ($current) {
            // Check which position this referral is in
            foreach ($positions as $pos => $member) {
                if ($member && $member->user_id == $current->id) {
                    // Found the referral in this circle - determine their section
                    if (in_array($pos, [1, 2, 3, 4, 5])) {
                        return ['section' => 1, 'positions' => [$positions[1], $positions[2], $positions[3], $positions[4]]];
                    } elseif (in_array($pos, [6, 7, 8, 9, 10])) {
                        return ['section' => 2, 'positions' => [$positions[6], $positions[7], $positions[8], $positions[9]]];
                    } elseif (in_array($pos, [11, 12, 13, 14, 15])) {
                        return ['section' => 3, 'positions' => [$positions[14], $positions[13], $positions[12], $positions[11]]];
                    } elseif (in_array($pos, [16, 17, 18, 19, 20])) {
                        return ['section' => 4, 'positions' => [$positions[19], $positions[18], $positions[17], $positions[16]]];
                    } elseif ($pos == 21) {
                        // Referral is in center (position 21) - can go to any section
                        return ['section' => 0, 'positions' => [
                            $positions[1], $positions[2], $positions[3], $positions[4],
                            $positions[6], $positions[7], $positions[8], $positions[9],
                            $positions[19], $positions[18], $positions[17], $positions[16],
                            $positions[14], $positions[13], $positions[12], $positions[11]
                        ]];
                    }
                }
            }
            // Move up the referral chain
            $current = $current->referal;
        }

        return null; // No referral found in this circle
    }

    /**
     * Check if user's referral chain includes someone in this circle
     * Returns true if any user in the referral chain is in the circle
     */
    private function combo_is_user_referral_in_circle($user, $circle_id)
    {
        $circle = ComboCircle::find($circle_id);
        if (!$circle) return false;

        // Get all user IDs in this circle
        $circle_user_ids = ComboMember::where('combo_circle_id', $circle_id)
            ->where('status', 'Occupied')
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->toArray();

        // Walk up the referral chain
        $current = $user->referal;
        while ($current) {
            if (in_array($current->id, $circle_user_ids)) {
                return true; // Found a referral in this circle
            }
            $current = $current->referal;
        }

        return false;
    }

    private function combo_fill_twentyone_directly($circle_id, $package_id, $user_id, $condition = 0)
    {
        $circle = ComboCircle::find($circle_id);
        $current_user = User::find($user_id);
        $package = Package::find($package_id);
        if (!$package) {
            return;
        }

        // Get the circle owner (referal) - this is the user who owns this circle
        $referal = User::find($circle->user_id);

        $occupied_count = ComboMember::where('combo_circle_id', $circle->id)->where('status', 'Occupied')->count();
        $first_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 1)->first();
        $second_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 2)->first();
        $third_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 3)->first();
        $fourth_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 4)->first();
        $fifth_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 5)->first();
        $six_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 6)->first();
        $seven_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 7)->first();
        $eight_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 8)->first();
        $nine_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 9)->first();
        $ten_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 10)->first();
        $eleven_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 11)->first();
        $twelve_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 12)->first();
        $thirteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 13)->first();
        $fourteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 14)->first();
        $fifteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 15)->first();
        $sixteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 16)->first();
        $seventeen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 17)->first();
        $eighteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 18)->first();
        $nineteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 19)->first();
        $twenty_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 20)->first();
        $twenty_one_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 21)->first();

        // EXACT match with original User.php 21-member logic (lines 820-895)
        // When occupied_count == 1: First downliner joins
        // New user → position 1, Owner → position 5
        if ($occupied_count == 1) {
            $first_position->user_id = $user_id;
            $first_position->status = 'Occupied';
            $first_position->save();

            $fifth_position->user_id = $referal->id;
            $fifth_position->status = 'Occupied';
            $fifth_position->save();
        }

        // When occupied_count == 2: Second downliner joins → position 2
        if ($occupied_count == 2) {
            $second_position->user_id = $user_id;
            $second_position->status = 'Occupied';
            $second_position->save();
        }

        // When occupied_count == 3: Third downliner joins → position 3
        if ($occupied_count == 3) {
            $third_position->user_id = $user_id;
            $third_position->status = 'Occupied';
            $third_position->save();
        }

        // When occupied_count == 4: Fourth downliner joins - MAJOR REARRANGEMENT
        // Owner → position 21 (center)
        // Position 1 user (user1) → position 5
        // Position 2 user (user2) → position 10
        // Position 3 user (user3) → position 15
        // New user (user4 - 4th downliner) → position 20
        // Clear positions 1,2,3,4
        if ($occupied_count == 4) {
            // Owner → position 21
            $twenty_one_position->user_id = $referal->id;
            $twenty_one_position->status = 'Occupied';
            $twenty_one_position->save();

            // Position 1 user (user1) → position 5
            $fifth_position->user_id = $first_position->user_id;
            $fifth_position->status = 'Occupied';
            $fifth_position->save();

            // Position 2 user (user2) → position 10
            $ten_position->user_id = $second_position->user_id;
            $ten_position->status = 'Occupied';
            $ten_position->save();

            // Position 3 user (user3) → position 15
            $fifteen_position->user_id = $third_position->user_id;
            $fifteen_position->status = 'Occupied';
            $fifteen_position->save();

            // New user (user4 - 4th downliner) → position 20
            $twenty_position->user_id = $user_id;
            $twenty_position->status = 'Occupied';
            $twenty_position->save();

            // Clear positions 1,2,3,4
            $first_position->user_id = null;
            $first_position->status = 'Empty';
            $first_position->save();

            $second_position->user_id = null;
            $second_position->status = 'Empty';
            $second_position->save();

            $third_position->user_id = null;
            $third_position->status = 'Empty';
            $third_position->save();

            $fourth_position->user_id = null;
            $fourth_position->status = 'Empty';
            $fourth_position->save();
        }

        // When occupied_count > 4 and < 21: Fill remaining positions based on referral section
        // EXACT match with original fill_directly() - check all positions in each section
        if ($occupied_count > 4 && $occupied_count < 21) {
            // Section 1: referrer is at position 5, 1, 2, or 3
            if ($fifth_position->user_id == $current_user->referal_id || $first_position->user_id == $current_user->referal_id
                || $second_position->user_id == $current_user->referal_id || $third_position->user_id == $current_user->referal_id) {
                $this->combo_assign_position([$first_position, $second_position, $third_position, $fourth_position, $six_position, $seven_position, $eight_position, $nine_position,
                    $nineteen_position, $eighteen_position, $seventeen_position, $sixteen_position,
                    $fourteen_position, $thirteen_position, $twelve_position, $eleven_position], $user_id);
            // Section 2: referrer is at position 10, 6, 7, or 8
            } elseif ($ten_position->user_id == $current_user->referal_id || $six_position->user_id == $current_user->referal_id
                || $seven_position->user_id == $current_user->referal_id || $eight_position->user_id == $current_user->referal_id) {
                $this->combo_assign_position([$six_position, $seven_position, $eight_position, $nine_position, $first_position, $second_position, $third_position, $fourth_position,
                    $nineteen_position, $eighteen_position, $seventeen_position, $sixteen_position,
                    $fourteen_position, $thirteen_position, $twelve_position, $eleven_position], $user_id);
            // Section 3: referrer is at position 15, 14, 13, or 12
            } elseif ($fifteen_position->user_id == $current_user->referal_id || $fourteen_position->user_id == $current_user->referal_id
                || $thirteen_position->user_id == $current_user->referal_id || $twelve_position->user_id == $current_user->referal_id) {
                $this->combo_assign_position([$fourteen_position, $thirteen_position, $twelve_position, $eleven_position, $first_position, $second_position, $third_position, $fourth_position,
                    $six_position, $seven_position, $eight_position, $nine_position, $nineteen_position, $eighteen_position, $seventeen_position, $sixteen_position], $user_id);
            // Section 4: referrer is at position 20, 19, 18, or 17
            } elseif ($twenty_position->user_id == $current_user->referal_id || $nineteen_position->user_id == $current_user->referal_id
                || $eighteen_position->user_id == $current_user->referal_id || $seventeen_position->user_id == $current_user->referal_id) {
                $this->combo_assign_position([$nineteen_position, $eighteen_position, $seventeen_position, $sixteen_position, $first_position, $second_position, $third_position, $fourth_position,
                    $six_position, $seven_position, $eight_position, $nine_position, $fourteen_position, $thirteen_position, $twelve_position, $eleven_position], $user_id);
            } else {
                // FALLBACK: Place in first available position (default order)
                $this->combo_assign_position([$first_position, $second_position, $third_position, $fourth_position, $six_position, $seven_position, $eight_position, $nine_position,
                    $nineteen_position, $eighteen_position, $seventeen_position, $sixteen_position, $fourteen_position, $thirteen_position, $twelve_position, $eleven_position], $user_id);
            }
        }

        $this->combo_is_section_completed_twentyone($circle_id, $package_id, $user_id);
        $this->combo_is_circle_completed_twentyone($circle_id, $package_id, $user_id);
    }

    private function combo_assign_position($positions, $user_id)
    {
        foreach ($positions as $position) {
            if ($position->status == 'Empty') {
                $position->user_id = $user_id;
                $position->status = 'Occupied';
                $position->save();
                return;
            }
        }
    }

    private function combo_is_section_completed_twentyone($circle_id, $package_id, $user_id)
    {
        $circle = ComboCircle::find($circle_id);
        $package = Package::find($package_id);
        if (!$package) {
            return;
        }

        $first_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 1)->first();
        $second_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 2)->first();
        $third_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 3)->first();
        $fourth_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 4)->first();
        $fifth_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 5)->first();
        $six_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 6)->first();
        $seven_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 7)->first();
        $eight_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 8)->first();
        $nine_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 9)->first();
        $ten_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 10)->first();
        $eleven_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 11)->first();
        $twelve_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 12)->first();
        $thirteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 13)->first();
        $fourteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 14)->first();
        $fifteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 15)->first();
        $sixteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 16)->first();
        $seventeen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 17)->first();
        $eighteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 18)->first();
        $nineteen_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 19)->first();
        $twenty_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 20)->first();
        $twenty_one_position = ComboMember::where('combo_circle_id', $circle->id)->where('position', 21)->first();

        if($first_position->status == 'Occupied' && $second_position->status == 'Occupied' && $third_position->status == 'Occupied' && $fourth_position->status == 'Occupied'
            && $fifth_position->status == 'Occupied' && $twenty_one_position->status == 'Occupied'){
            $new_circle = ComboCircle::where('user_id',$fifth_position->user_id)->where('status','Active')->where('package_id',$package->id)->where('section','twentyone')->first();
            if(!$new_circle){
                $owner = User::find($fifth_position->user_id);
                $new_circle = $owner ? $owner->create_combo_circle($package->id, 'twentyone', 1, false) : null;
                if($new_circle){
                    $this->combo_seed_twentyone_new_circle($new_circle, $first_position->user_id, $second_position->user_id, $third_position->user_id, $fourth_position->user_id, $fifth_position->user_id);
                    $this->combo_debit_autorenew($owner, $package->combo_twentyone_autorenew_amount, 'Combo 21-Member Auto-Renew');
                }
            }
            $this->combo_give_twentyone_reward($circle, 1, $package);
        }

        if($six_position->status == 'Occupied' && $seven_position->status == 'Occupied' && $eight_position->status == 'Occupied' && $nine_position->status == 'Occupied'
            && $ten_position->status == 'Occupied' && $twenty_one_position->status == 'Occupied'){
            $new_circle = ComboCircle::where('user_id',$ten_position->user_id)->where('status','Active')->where('package_id',$package->id)->where('section','twentyone')->first();
            if(!$new_circle){
                $owner = User::find($ten_position->user_id);
                $new_circle = $owner ? $owner->create_combo_circle($package->id, 'twentyone', 1, false) : null;
                if($new_circle){
                    $this->combo_seed_twentyone_new_circle($new_circle, $six_position->user_id, $seven_position->user_id, $eight_position->user_id, $nine_position->user_id, $ten_position->user_id);
                    $this->combo_debit_autorenew($owner, $package->combo_twentyone_autorenew_amount, 'Combo 21-Member Auto-Renew');
                }
            }
            $this->combo_give_twentyone_reward($circle, 2, $package);
        }

        if($eleven_position->status == 'Occupied' && $twelve_position->status == 'Occupied' && $thirteen_position->status == 'Occupied'
            && $fourteen_position->status == 'Occupied' && $fifteen_position->status == 'Occupied' && $twenty_one_position->status == 'Occupied'){
            $new_circle = ComboCircle::where('user_id',$fifteen_position->user_id)->where('status','Active')->where('package_id',$package->id)->where('section','twentyone')->first();
            if(!$new_circle){
                $owner = User::find($fifteen_position->user_id);
                $new_circle = $owner ? $owner->create_combo_circle($package->id, 'twentyone', 1, false) : null;
                if($new_circle){
                    $this->combo_seed_twentyone_new_circle($new_circle, $fourteen_position->user_id, $thirteen_position->user_id, $twelve_position->user_id, $eleven_position->user_id, $fifteen_position->user_id);
                    $this->combo_debit_autorenew($owner, $package->combo_twentyone_autorenew_amount, 'Combo 21-Member Auto-Renew');
                }
            }
            $this->combo_give_twentyone_reward($circle, 3, $package);
        }

        if($sixteen_position->status == 'Occupied' && $seventeen_position->status == 'Occupied' && $eighteen_position->status == 'Occupied'
            && $nineteen_position->status == 'Occupied' && $twenty_position->status == 'Occupied' && $twenty_one_position->status == 'Occupied'){
            $new_circle = ComboCircle::where('user_id',$twenty_position->user_id)->where('status','Active')->where('package_id',$package->id)->where('section','twentyone')->first();
            if(!$new_circle){
                $owner = User::find($twenty_position->user_id);
                $new_circle = $owner ? $owner->create_combo_circle($package->id, 'twentyone', 1, false) : null;
                if($new_circle){
                    $this->combo_seed_twentyone_new_circle($new_circle, $nineteen_position->user_id, $eighteen_position->user_id, $seventeen_position->user_id, $sixteen_position->user_id, $twenty_position->user_id);
                    $this->combo_debit_autorenew($owner, $package->combo_twentyone_autorenew_amount, 'Combo 21-Member Auto-Renew');
                }
            }
            $this->combo_give_twentyone_reward($circle, 4, $package);
        }
    }

    private function combo_seed_twentyone_new_circle($circle, $pos1, $pos2, $pos3, $pos4, $pos5)
    {
        $positions = [
            5 => $pos1,
            10 => $pos2,
            15 => $pos3,
            20 => $pos4,
            21 => $pos5,
        ];
        foreach($positions as $position => $user_id){
            $member = ComboMember::where('combo_circle_id', $circle->id)->where('position', $position)->first();
            if(!$member){
                $member = new ComboMember();
                $member->combo_circle_id = $circle->id;
                $member->position = $position;
                $member->package_id = $circle->package_id;
            }
            $member->user_id = $user_id;
            $member->status = 'Occupied';
            $member->save();
        }
    }

    private function combo_give_twentyone_reward($circle, $section, $package)
    {
        $circle_reward = ComboCircleReward::where('combo_circle_id', $circle->id)->where('section', $section)->first();
        if($circle_reward){
            return;
        }

        $downlines = $circle->user->downlines;
        $purchsed_packages_count = 0;
        foreach($downlines as $downline){
            $downline_circle = Subscription::where('package_id',$package->id)->where('user_id',$downline->id)->first();
            if($downline_circle){
                $purchsed_packages_count = $purchsed_packages_count + 1;
            }
            if($purchsed_packages_count >= 4){
                break;
            }
        }

        if($purchsed_packages_count >= 4){
            $reward_amount = $package->combo_twentyone_reward_amount;
            $bonus = ($reward_amount * 10) / 100;
            $total_reward = $reward_amount + $bonus;

            $circle->user->combo_wallet = ($circle->user->combo_wallet ?? 0) + $total_reward;
            $circle->user->save();

            $this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->combo_wallet, "Combo 21-Member Section {$section} completed");

            $circle_reward = new ComboCircleReward();
            $circle_reward->user_id = $circle->user_id;
            $circle_reward->combo_circle_id = $circle->id;
            $circle_reward->amount = $reward_amount;
            $circle_reward->section = $section;
            $circle_reward->desc = "{$section} Section completed";
            $circle_reward->status = 'Success';
            $circle_reward->save();
        }
    }

    private function combo_is_circle_completed_twentyone($circle_id, $package_id, $user_id)
    {
        $circle = ComboCircle::find($circle_id);
        $package = Package::find($package_id);
        if (!$package || !$circle) {
            return;
        }

        $occupied_count = ComboMember::where('combo_circle_id', $circle->id)->where('status', 'Occupied')->count();
        if ($occupied_count == 21) {
            $circle->status = 'Completed';
            $circle->save();

            $circle->user->combo_wallet = ($circle->user->combo_wallet ?? 0) - $package->combo_twentyone_reward_amount;
            $circle->user->save();
            $this->create_transaction($circle->user_id, 'Debit', $package->combo_twentyone_reward_amount, $circle->user->combo_wallet, $package->name.' Combo 21-Member Auto Purchase');

            $upline_circle = $this->combo_find_upline_circle($circle->user, $package->id);
            if ($upline_circle) {
                $this->combo_fill_twentyone_directly($upline_circle->id, $package_id, $circle->user_id);
                return;
            }

            foreach ($circle->user->downlines as $downline) {
                $downline_circle = ComboCircle::where('package_id', $package->id)
                    ->where('section', 'twentyone')
                    ->where('user_id', $downline->id)
                    ->where('status', 'Active')
                    ->first();

                if ($downline_circle) {
                    $this->combo_fill_twentyone_directly($downline_circle->id, $package_id, $circle->user_id);
                    return true;
                }
            }

            $upline = $circle->user->referal;
            if ($upline) {
                foreach ($upline->downlines as $downline) {
                    $downline_circle = ComboCircle::where('package_id', $package->id)
                        ->where('section', 'twentyone')
                        ->where('user_id', $downline->id)
                        ->where('status', 'Active')
                        ->first();

                    if ($downline_circle) {
                        $this->combo_fill_twentyone_directly($downline_circle->id, $package_id, $circle->user_id);
                        return true;
                    }
                }
            }
        }
    }

    /**
     * Get user details by referral code
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function get_user_by_referral(Request $request)
    {
        $rules = [
            'referal_code' => 'required|string|exists:users,referal_code',
        ];
        
        $validation = \Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json([
                'error' => $validation->errors()->first()
            ], 422);
        }

        $user = User::where('referal_code', $request->referal_code)
            ->with(['country', 'state', 'district', 'mandal'])
            ->first();

        if (!$user) {
            return response()->json([
                'error' => 'User not found with this referral code'
            ], 404);
        }

        // Get referal information if exists
        $user->referal_id = $user->referal ? $user->referal->referal_code : 'N/A';

        return response()->json([
            'user' => $user,
            'message' => 'User details retrieved successfully'
        ], 200);
    }

    
}
