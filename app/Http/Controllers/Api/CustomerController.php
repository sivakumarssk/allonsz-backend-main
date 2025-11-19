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
        return response()->json([
            'user' => $user
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
            $file= $request->file('photo');
            $allowedfileExtension=['JPEG','jpeg','jpg','png'];
            $extension = $file->getClientOriginalExtension();
            $check = in_array($extension,$allowedfileExtension);
            if($check){
                $oldFilePath = public_path('/images/profiles/'.$user->photo_filename);
                if (file_exists($oldFilePath) && $user->photo_filename != '') {
                    unlink($oldFilePath);
                }
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $filename = substr(str_shuffle(str_repeat($pool, 5)), 0, 12) .'.'.$extension;
                $path = $file->move(public_path('/images/profiles'), $filename);
                $user->photo = $filename;
            }else{
                return response()->json([
                    'error' => 'Invalid file format, please upload valid image file'
                ], 422);
            }
        }
        $referal = User::where('referal_code',$request->referal_code)->first();
        $member = Member::where('user_id',$referal->id)->first();
        if(!$member){
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
            'photo' => 'required|image',
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
            $file= $request->file('photo');
            $allowedfileExtension=['JPEG','jpeg','jpg','png'];
            $extension = $file->getClientOriginalExtension();
            $check = in_array($extension,$allowedfileExtension);
            // if($check){
                $oldFilePath = public_path('/images/profiles/'.$user->photo_filename);
                if (file_exists($oldFilePath) && $user->photo_filename != '') {
                    unlink($oldFilePath);
                }
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $filename = substr(str_shuffle(str_repeat($pool, 5)), 0, 12) .'.'.$extension;
                $path = $file->move(public_path('/images/profiles'), $filename);
                $user->photo = $filename;
            // }else{
            //     return response()->json([
            //         'error' => 'Invalid file format, please upload valid image file'
            //     ], 422);
            // }
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
        $upline = $user->referal;
        $memberIds = Member::where('user_id', $upline->id)
            ->selectRaw('MAX(id) as id')
            ->groupBy('package_id')
            ->pluck('id');

        $circleds = Member::whereIn('id', $memberIds)
            ->where('user_id',$upline->id)
                ->orderBy('id', 'desc')
                ->pluck('circle_id');

        $circles = Circle::whereIn('id',$circleds)->with(['package','members'])->get();
            $packageIds = $circles->pluck('package_id')->unique()->values();

        if($packageIds){
            $packages = Package::whereIn('id',$packageIds)->get();
        }
        $subscription = Member::where('user_id',$user->id)->first();
        if($subscription){
            $packages = Package::all();
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
        $circles = Auth::User()->active_circles();
        foreach($circles as $circle){
            $timer = Timer::where('user_id',Auth::User()->id)->where('package_id',$circle->package_id)->first();
            $purchased_at = $timer->started_at;
            $expiresAt = $purchased_at->copy()->addDays(120); // Changed from 60 to 120 days (4 months)
            $circle['purchased_at'] = $expiresAt;
            foreach($circle->members as $member){
                $color = Color::where('package_id',$circle->package_id)->where('position',$member->position)->first();
                $member['color'] = $color->color;
                $member['is_downline'] = false;
                if($member->user)
                {
                    if($member->user->referal_id == Auth::User()->id || $member->user_id == Auth::User()->id){
                        $member['is_downline'] = true;
                    }
                }
                
            }
        }
        return response()->json([
                'circles' => $circles
        ],200);
    }
    
    public function get_completed_circles(Request $request)
    {
        $circles = Auth::User()->completed_circles;
        foreach($circles as $circle){
            foreach($circle->members as $member){
                $color = Color::where('package_id',$circle->package_id)->where('position',$member->position)->first();
                $member['color'] = $color->color;
            }
        }
        return response()->json([
                'circles' => $circles
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
        $item_name = $package->name;
        $item_number = $package->id;
        $item_amount = $package->price;

        $setting = Setting::first();
        $sgst = (($setting->sgst * $package->price) / 100);
        $cgst = (($setting->cgst * $package->price) / 100);
        $total =  $sgst + $cgst + $package->price;

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
        $order->status = 'Created';
        $order->save();
        
        return response()->json([
                'data' => $data,
                'displayCurrency' => $displayCurrency
        ],200);
    }
    public function verify_razorpay_signature(Request $request)
    {
        $rules = [
            'razorpay_order_id' => 'required',
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
        
        try{
            $attributes = array(
                'razorpay_order_id' => $razorpay_order_id,
                'razorpay_payment_id' => $razorpay_payment_id,
                'razorpay_signature' => $razorpay_signature
            );
            // if(!$request->type){
                $this->api->utility->verifyPaymentSignature($attributes);
            // }
            
        }
        catch(SignatureVerificationError $e){
            $success = false;
            $error = 'Razorpay Error : ' . $e->getMessage();
            return response()->json([
                    'error' => $error
            ],400);
        }
        if ($success === true || $request->type == 'manual')
        {
            $razorpayOrder = $this->api->order->fetch($razorpay_order_id);
            $reciept = $razorpayOrder['receipt'];
            $transaction_id = $razorpay_payment_id;
            
            $order->payment_id = $razorpay_payment_id;
            $order->signature = $razorpay_signature;
            $order->status = 'Verified';
            $order->save();
            
            $subscription = new Subscription();
            $subscription->user_id = $user->id;
            $subscription->package_id = $order->package_id;
            $subscription->status = 'Active';
            $subscription->save();
            
            $package = Package::find($order->package_id);
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
            //$user->update_circle_member($package->id,Auth::User()->id);
            // $user->wallet = $user->wallet + $package->price;
            // $user->save();
            
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

            $package = Package::find($order->package_id);
            
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
    
    public function withdraw_history()
    {
        $user = Auth::User();
        $withdraws = $user->withdraws;
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

                                $this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '1st Section completed (Base: ₹'.$base_reward.' + 10% Bonus: ₹'.$bonus.')');
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

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '2nd Section completed (Base: ₹'.$base_reward.' + 10% Bonus: ₹'.$bonus.')');
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

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '1st Section completed (Base: ₹'.$base_reward.' + 10% Bonus: ₹'.$bonus.')');
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

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '2nd Section completed (Base: ₹'.$base_reward.' + 10% Bonus: ₹'.$bonus.')');
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

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '3rd Section completed (Base: ₹'.$base_reward.' + 10% Bonus: ₹'.$bonus.')');
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

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '1st Section completed (Base: ₹'.$base_reward.' + 10% Bonus: ₹'.$bonus.')');
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

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '2nd Section completed (Base: ₹'.$base_reward.' + 10% Bonus: ₹'.$bonus.')');
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

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '3rd Section completed (Base: ₹'.$base_reward.' + 10% Bonus: ₹'.$bonus.')');
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

$this->create_transaction($circle->user_id, 'Credit', $total_reward, $circle->user->wallet, '4th Section completed (Base: ₹'.$base_reward.' + 10% Bonus: ₹'.$bonus.')');
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

    
}
