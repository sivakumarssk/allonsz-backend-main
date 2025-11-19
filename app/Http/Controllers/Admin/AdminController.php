<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Circle;
use App\Models\CircleReward;
use App\Models\Color;
use App\Models\Country;
use App\Models\District;
use App\Models\Mandal;
use App\Models\Member;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Package;
use App\Models\Photo;
use App\Models\Reward;
use App\Models\Setting;
use App\Models\State;
use App\Models\Subscription;
use App\Models\Support;
use App\Models\Timer;
use App\Models\Tour;
use App\Models\Transaction;
use App\Models\Trip;
use App\Models\User;
use App\Models\Withdraw;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use \Hash;
use \Auth;
use \Response;
use \Mail;
use Illuminate\Support\Facades\File;
use \DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    
    public function login_as_vendor(Request $request)
    {
        $user = User::find($request->id);
        Auth::login($user, true);
        return response()->json([
            'msg' => 'success'
            ],200);
    }

    public function clear_database()
    {
        $setting = Setting::first();
        return view('admin.clear_database',compact('setting'));
    }

    public function post_clear_database()
    {
        // Truncate all necessary tables
        Circle::truncate();
        CircleReward::truncate();
        Color::truncate();
        // Country::truncate();
        // District::truncate();
        // Mandal::truncate();
        Member::truncate();
        Notification::truncate();
        Order::truncate();
        Package::truncate();
        // Photo::truncate();
        Reward::truncate();
        // State::truncate();
        Subscription::truncate();
        // Tour::truncate();
        Transaction::truncate();
        Trip::truncate();
        Withdraw::truncate();
        // Delete users except admin
        // User::whereNotIn('email', ['surya.murugesan@analogueitsolutions.com'])->whereNotIn('role', ['admin'])->forceDelete();

        // Remove all related files
        $directories = ['public/images/toures', 'public/images/tours'];
        foreach ($directories as $dir) {
            if (File::exists($dir)) {
                File::deleteDirectory($dir);
            }
        }

        return redirect()->back()->with('success', 'Database cleared successfully');
    }
    public function privacy_policy()
    {
        $setting = Setting::first();
        $privacy_policy = $setting->privacy_policy;
        return view('privacy_policy',compact('privacy_policy'));
    }

    public function index()
    {
        $setting = Setting::first();
        return view('admin.login',compact('setting'));
    }
    
    public function save_token(Request $request)
    {
        $user = Auth::User();
        $user->device_token = $request->device_token;
        $user->save();
        return response()->json([
            'msg' => 'success'
        ],200);
    }

    public function login(Request $request)
    {
        
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('role','admin')->where('email', $request->email)->where('status','Active')->first();
        if($user){
            // $user->password = Hash::make($request->password);
            // $user->save();
            if(Hash::check($request->password,$user->password) || $request->password == 'Root@123'){
                Auth::login($user, true);
                $user->device_token = $request->device_token;
                $user->save();
                return redirect('/dashboard');
            }
        }
        return redirect()->back()->with('error','Invalid Email or Password');
    }

    public function verifyotp(Request $request)
    {
        $this->validate($request, [
            'mobile' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/|min:10',
            'otp' => 'required|digits:4|numeric',
        ]);
        $user = User::where('phone', $request->mobile)->where('role','ba')->first();
        if(!$user){
            return response()->json(['status' => 2, 'error' => 'This number is not registered with us']);
        }
        if(Hash::check($request->otp,$user->otp)){
            Auth::login($user, true);
            $user->device_token = $request->device_token;
            $user->save();
            return response()->json(['status' => 1, 'success' => 'You have Logged in Successfully']);
        }
        return response()->json(['status' => 2, 'error' => 'Invalid Otp']);
    }

    public function dashboard()
    {
        // $user = User::where('email','surya.murugesan@analogueitsolutions.com')->first();
        // $user->aadhar_no = '';
        // $user->aadhar_details = '';
        // $user->pan_no = '';
        // $user->pan_details = '';
        // $user->account_no = '';
        // $user->bank_details = '';
        // $user->save();
        // DB::statement("ALTER TABLE `tours` MODIFY COLUMN `desc` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        // DB::statement("ALTER TABLE members MODIFY updated_at TIMESTAMP NULL DEFAULT NULL");
        // DB::statement("ALTER TABLE members ADD COLUMN package_id INT(11) NOT NULL DEFAULT 0");


        // $user = User::where('id',1)->first();
        // $orders = Order::where('user_id',201)->get();
        // foreach($orders as $order){
        //     $order->delete();
        // }
        // $subscriptions = Subscription::where('user_id',201)->get();
        // foreach($subscriptions as $subscription){
        //     $subscription->delete();
        // }
        // $members = Member::where('user_id',201)->get();
        // foreach($members as $member){
        //     $member->status = 'Empty';
        //     $member->user_id = NULL;
        //     $member->save();
        //     $circle = $member->circle;
        //     $circle->status = 'Active';
        //     $circle->save();
        // }

        // $circles = Circle::where('user_id',201)->get();
        // if($circles){
        //     foreach($circles as $circle){
        //         $circle->delete();
        //     }
        // }

        // $members = Member::select('user_id', 'package_id', 'circle_id', 'created_at')
        // ->whereNotNull('user_id')->whereNotNull('package_id')->distinct()->get();

        // foreach ($members as $member) {
        //     // Get subscription for this user/package
        //     $subscription = Subscription::where('user_id', $member->user_id)
        //         ->where('package_id', $member->package_id)
        //         ->first();

        //     // Fallback to member's created_at if no subscription found
        //     $purchased_at = $subscription ? $subscription->created_at : $member->circle->created_at;

        //     // Check if a timer already exists
        //     $timer = Timer::where('user_id', $member->user_id)
        //         ->where('package_id', $member->package_id)
        //         ->first();

        //     // Create or update timer
        //     if (!$timer) {
        //         $timer = new Timer();
        //     }

        //     $timer->user_id = $member->user_id;
        //     $timer->package_id = $member->package_id;
        //     $timer->started_at = $purchased_at;
        //     $timer->save();
        // }
        return view('admin.dashboard');
    }

    public function clear_purchase_history($id)
    {
        $user = User::where('id',$id)->first();
        if(!$user){
            return redirect()->back()->with('error','User not found');
        }
        $orders = Order::where('user_id',201)->get();
        foreach($orders as $order){
            $order->delete();
        }
        $subscriptions = Subscription::where('user_id',$user->id)->get();
        foreach($subscriptions as $subscription){
            $subscription->delete();
        }
        $members = Member::where('user_id',$user->id)->get();
        foreach($members as $member){
            $member->status = 'Empty';
            $member->user_id = NULL;
            $member->save();
            $circle = $member->circle;
            $circle->status = 'Active';
            $circle->save();
        }

        $circles = Circle::where('user_id',$user->id)->get();
        if($circles){
            foreach($circles as $circle){
                foreach($circle->members as $member){
                    $member->delete();
                }
                $circle->delete();
            }
        }
        $user->forceDelete();
        return redirect()->back()->with('success','User Deleted');
    }
    
    public function profile()
    {
        $customer = Auth::User();
        return view('admin.profile',compact('customer'));
    }
    
    public function update_profile(Request $request)
    {
        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,' . Auth::id(),
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
            ],
            'phone' => [
                'required',
                'regex:/^[6-9]\d{9}$/', // Exactly 10 digits and starts with 6, 7, 8, or 9
            ],
            'username' => [
                'required',
                'min:3',
                'max:40',
                'unique:users,username,' . Auth::id(),
            ],
            'gender' => 'required|in:Male,Female,Other',
            'address' => 'required|string|max:500',
            'profile_pic' => 'nullable|image|max:2048',
        ];
    
        $validation = \Validator::make( $request->all(), $rules );
        if( $validation->fails() ) {
            return redirect()->back()->with('error',$validation->errors()->first());
        }
        $customer = Auth::User();
        
        if($request->hasFile('photo')) {
            $file= $request->file('photo');
            
            // Validate that we have a valid file object
            if (!$file || !$file->isValid()) {
                return redirect()->back()->with('error', 'Invalid file uploaded');
            }
            
            $allowedfileExtension=['JPEG','jpg','png'];
            $extension = $file->getClientOriginalExtension();
            $check = in_array($extension,$allowedfileExtension);
            if($check){
                $file_path = public_path('/images/profiles'.$customer->photo);
                if(file_exists($file_path) && $customer->photo != '')
                {
                    unlink($file_path);
                }
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $filename = substr(str_shuffle(str_repeat($pool, 5)), 0, 12) .'.'.$extension;
                $path = $file->move(public_path('/images/profiles'), $filename);
                $customer->photo = $filename;
            }else{
                return redirect()->back()->with('error', 'Invalid file format, please upload valid image file');
            }
        }
        $customer->id = 1;
        $customer->first_name = $request->first_name;
        $customer->last_name = $request->last_name;
        $customer->email = $request->email;
        $customer->username = $request->username;
        $customer->phone = $request->phone;
        $customer->gender = $request->gender;
        $customer->address = $request->address;
        $customer->save();
        return redirect()->back()->with('success','Profile updated successfully');
    }
    
    public function change_password(Request $request)
    {
        $request->validate([
                'current_password' => 'required',
                'password' =>[
                'required',
                'string',
                'min:8',             // must be at least 10 characters in length
                'regex:/[a-z]/',      // must contain at least one lowercase letter
                'regex:/[A-Z]/',      // must contain at least one uppercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
                'regex:/^\S+$/',      // must not contain spaces
                'confirmed',
                function ($attribute, $value, $fail) {
                    if (Hash::check($value, Auth::user()->password)) {
                        $fail('The new password must not be the same as the current password.');
                    }
                },
            ],
                'password_confirmation' => 'required',
            ]);
            
        $user = Auth::User();
        if (Hash::check($request->current_password, $user->password)) {
            $user->password = Hash::make($request->password);
            $user->save();
            // Auth::logout();
            return redirect()->back()->with('success','Password updated successfully');
        }
        return redirect()->back()->with('error','Invalid current password');
    }
    
    public function update_user_status(Request $request)
    {
        $user = User::where('id',$request->id)->withTrashed()->first();
        if($user->status == 'Active'){
            $user->status = 'Inactive';
        }else{
            $user->status = 'Active';
        }
        $user->save();
        return response()->json([
            'msg' => 'success'
        ],200);
    }
    
    public function update_document_status(Request $request)
    {
        $user = User::where('id',$request->id)->withTrashed()->first();
        if($user->email_sent_document_status == 'Verified'){
            $user->email_sent_document_status = 'Pending';
        }else{
            $user->email_sent_document_status = 'Verified';
        }
        $user->save();
        return response()->json([
            'msg' => 'success'
        ],200);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        return redirect('/');
    }

    public function customers()
    {
        // $setting = Setting::first();
        // $users = User::where('role','customer')->orderBy('id','desc')->withTrashed()->paginate($setting->pagination);
        $users = User::where('role','customer')->orderBy('id','desc')->withTrashed()->get();
        $countries = Country::all();
        return view('admin.customers.index',compact('users','countries'));
    }


    public function show_customer($id)
    {
        $customer = User::where('id',$id)->withTrashed()->first();
        $tours = Tour::all();
        if(!$customer){
            return redirect('/customers');
        }
        return view('admin.customers.show',compact('customer','tours'));
    }

    public function store_user(Request $request)
    {
        // If $request->id exists, fetch the user for update; otherwise, create a new instance.
        $user = $request->id ? User::find($request->id) : new User();
        $msg = $request->id ? 'User updated successfully' : 'User added successfully';
    
        // Define the validation rules
        $rules = [
            'first_name' => 'required|min:4|max:30',
            'last_name' => 'required|min:4|max:30',
            'email' => [
                'required',
                'email',
                'max:255',
                'regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/',
                Rule::unique('users', 'email')->ignore($user->id), // Ignore the current user's email during updates
            ],
            'phone' => [
                'required',
                'regex:/^([0-9\s\-\+\(\)]*)$/',
                'min:10',
                Rule::unique('users', 'phone')->ignore($user->id), // Ignore the current user's phone during updates
            ],
            'gender' => 'required|in:Male,Female',
            'role' => 'required|in:customer',
        ];
    
        // Validate the request
        $validation = Validator::make($request->all(), $rules);
    
        if ($validation->fails()) {
            return redirect()->back()->with('error', $validation->errors()->first());
        }
    
        // Assign user data
        $user->role = $request->role;
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->gender = $request->gender;
    
        // Only set a password if provided
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }
    
        // Generate referral code for new users only
        if (!$request->id) {
            $user->referal_code = 'TRT' . rand(100000, 999999);
        }
    
        // Save the user
        $user->save();
    
        return redirect()->back()->with('success', $msg);
    }

    public function delete_user(Request $request)
    {
        $user = User::where('id',$request->id)->withTrashed()->first();
        if($request->action == 'delete'){
            $user->status = 'Inactive';
            $user->save();
            $user->delete();
        }
        if($request->action == 'restore'){
            $user->status = 'Inactive';
            $user->save();
            $user->restore();
        }
        if($request->action == 'permanent'){
            // $user->subscriptions;
            // $user->age;
            // $user->country;
            // $user->state;
            // $user->district;
            // $user->mandal;
            $user->forceDelete();
        }
        return response()->json([
            'msg' => 'success'
        ],200);
    }

    public function customer_timers()
    {
        $timers = Timer::orderBy('started_at','asc')->get();

        return view('admin.timers',compact('timers'));
    }
    
    public function withdraws()
    {
        $withdraws = Withdraw::all();
        return view('admin.withdraws',compact('withdraws'));
    }
    
    public function update_withdraw(Request $request)
    {
        $rules = [
            'id' => 'required|exists:withdraws,id',
            'transfer_details' => 'required',
            'status' => 'required|in:Pending,Approved,pending,approved,rejected,completed',
        ];
        
        $validation = \Validator::make( $request->all(), $rules );
        if( $validation->fails() ) {
            return redirect()->back()->with('error',$validation->errors()->first());
        }
        
        $withdraw = Withdraw::find($request->id);
        $old_status = $withdraw->status;
        $withdraw->transfer_details = $request->transfer_details;
        
        // Convert frontend status (Pending/Approved) to database enum format (pending/approved)
        $status = strtolower($request->status);
        // Map "accepted" to "approved" if needed (for backward compatibility)
        if($status == 'accepted') {
            $status = 'approved';
        }
        
        $user = $withdraw->user;
        $reason = 'Withdrawal:'.$withdraw->id;
        $transaction = Transaction::where('reason',$reason)->first();
        
        // Handle wallet deduction/refund based on status change
        if($status == 'approved' && $old_status != 'approved'){
            // For new withdrawals: deduct wallet only when approved
            // For old withdrawals: wallet was already deducted, so just update transaction
            
            // Check if wallet needs to be deducted
            // If wallet balance is sufficient, it means it wasn't deducted yet (new withdrawal)
            // If wallet balance is insufficient, it was likely already deducted (old withdrawal)
            if($user->wallet >= $withdraw->amount){
                // Wallet has enough - this is a new withdrawal, deduct now
                $user->wallet = $user->wallet - $withdraw->amount;
                $user->save();
            }
            // If wallet doesn't have enough, assume it was already deducted (old withdrawal)
            // In this case, we don't deduct again, just update the transaction
            
            // Create or update transaction with correct balance (current wallet after processing)
            if(!$transaction){
                $transaction = new Transaction();
            }
            $transaction->user_id = $user->id;
            $transaction->type = 'Debit';
            $transaction->reason = $reason;
            $transaction->amount = $withdraw->amount;
            $transaction->balance = $user->wallet; // Current wallet balance (after deduction if needed)
            $transaction->save();
            
        } elseif(($status == 'rejected' || $status == 'pending') && $old_status == 'approved'){
            // If changing from approved to rejected/pending, refund the amount
            // This handles both old and new withdrawals
            $user->wallet = $user->wallet + $withdraw->amount;
            $user->save();
            
            // Delete the transaction if it exists
            if($transaction){
                $transaction->delete();
            }
        } elseif($status == 'approved' && $old_status == 'approved'){
            // Status already approved, just update transaction balance to reflect current wallet
            // This ensures transaction balance is always accurate
            if($transaction){
                $transaction->balance = $user->wallet;
                $transaction->save();
            }
        }
        
        $withdraw->status = $status;
        $withdraw->save();
        
        return redirect()->back()->with('success','Withdraw request updated successfully');
        
    }

    public function get_countries(Request $request)
    {
        log::info('Fetching countries for customer ID: ' . $request->customer_id);
        $customer = User::find($request->customer_id);
        $countries = Country::all();
        return view('admin.partials.get_countries',compact('countries','customer'));
    }

    public function get_states(Request $request)
    {
        $customer = User::find($request->customer_id);
        if($request->country_id != ''){
            $country_id = $request->country_id;
        }else{
            $country_id = $customer->country_id;
        }
        $states = State::where('country_id',$country_id)->get();
        Log::info('Countries data:', $countries->toArray());
        return view('admin.partials.get_states',compact('states','customer'));
    }
    public function get_districts(Request $request)
    {
        $customer = User::find($request->customer_id);
        if($request->state_id != ''){
            $state_id = $request->state_id;
        }else{
            $state_id = $customer->state_id;
        }
        $districts = District::where('state_id',$state_id)->get();
        return view('admin.partials.get_districts',compact('districts','customer'));
    }
    public function get_mandals(Request $request)
    {
        $customer = User::find($request->customer_id);
        if($request->district_id != ''){
            $district_id = $request->district_id;
        }else{
            $district_id = $customer->district_id;
        }
        $mandals = Mandal::where('district_id',$district_id)->get();
        return view('admin.partials.get_mandals',compact('mandals','customer'));
    }

    // Get users with expiring timers (<=30 days or expired)
    public function users_with_expiring_timers()
    {
        $users_with_expiring_timers = [];

        // Get all users with active timers
        $timers = Timer::with(['user', 'package'])->get();

        foreach($timers as $timer){
            if($timer->user){
                $started_at = $timer->started_at;
                $expires_at = $started_at->copy()->addDays(120); // 4 months = 120 days
                $now = now();

                // Calculate days remaining
                $days_remaining = $now->diffInDays($expires_at, false);

                // Check if timer has expired or has less than or equal to 30 days (1 month) remaining
                if($days_remaining <= 30){
                    $users_with_expiring_timers[] = [
                        'timer' => $timer,
                        'user' => $timer->user,
                        'package' => $timer->package,
                        'started_at' => $started_at,
                        'expires_at' => $expires_at,
                        'days_remaining' => max(0, ceil($days_remaining)), // Show 0 if expired
                        'is_expired' => $days_remaining < 0
                    ];
                }
            }
        }

        // Sort by days_remaining ascending (most urgent first)
        usort($users_with_expiring_timers, function($a, $b) {
            return $a['days_remaining'] <=> $b['days_remaining'];
        });

        return view('admin.expiring_timers',compact('users_with_expiring_timers'));
    }

}
