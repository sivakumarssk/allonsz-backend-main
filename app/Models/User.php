<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Package;
use App\Models\Circle;
use App\Models\CircleReward;
use App\Models\Trip;
use App\Models\Subscription;
use App\Models\Member;
use \Str;
use Illuminate\Support\Facades\Log;
use \Auth;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $hidden = [
        'password', 'remember_token','api_token','device_token','otp',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    public function sent_notifications()
    {
        return $this->hasMany('App\Models\Notification','from_id');
    }
    
    public function getNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
    
    public function setEmailAttribute($value)
    {
        $this->attributes['email'] = strtolower($value);
    }
    
    public function getEmailAttribute($value)
    {
        return strtolower($value);
    }
    
    public function getPhotoAttribute($value)
    {
        if($value){
            return asset('images/profiles/'.$value);
        }else{
            if($this->gender == 'Male'){
                return asset('images/profiles/male.png');
            }
            if($this->gender == 'Female' && $value == ''){
                return asset('images/profiles/female.jpeg');
            }
        }
        return asset('images/profiles/male.png');
    }
    
    public function getPhotoFilenameAttribute()
    {
        return $this->attributes['photo'];
    }
    
    public function subscriptions()
    {
        return $this->belongsToMany('\App\Models\Package', 'subscriptions', 'user_id', 'package_id');
    }
    
    public function age()
    {
        return $this->belongsTo('App\Models\Age');
    }
    
    public function country()
    {
        return $this->belongsTo('App\Models\Country');
    }
    
    public function state()
    {
        return $this->belongsTo('App\Models\State');
    }
    
    public function district()
    {
        return $this->belongsTo('App\Models\District');
    }
    
    public function mandal()
    {
        return $this->belongsTo('App\Models\Mandal');
    }
    
    public function circles()
    {
        return $this->hasMany('App\Models\Circle')
            ->orderBy('id', 'desc')
            ->with(['package', 'members']);
    }
    
    public function getCirclesAttribute()
    {
        $circles = $this->circles()->get();
        // $member = Member::where('user_id',$this->id)->orderBy('updated_at','desc')->first();
        // if($member){
        //     if($member->circle->user_id != $this->id){
        //         $circle = Circle::where('id',$member->circle_id)->with(['package', 'members'])->first();
        //         if ($circle) {
        //             $circles->prepend($circle);
        //         }
        //     }
        // }
        
        $members = Member::where('user_id', $this->id)
            ->whereHas('circle.package') // Ensure the member has a circle with a package
            ->orderBy('updated_at', 'desc') // Get the latest members first
            ->get()
            ->unique(function ($member) {
                return $member->circle->package->id; // Ensure unique package IDs
            });
            
        foreach($members as $member){
            if($member->circle->user_id != $this->id){
                $circle = Circle::where('id',$member->circle_id)->with(['package', 'members'])->first();
                if ($circle) {
                    $circles->prepend($circle);
                }
            }
        }
    
        return $circles;
    }
    
    public function active_circles()
    {
        $memberIds = Member::where('user_id', $this->id)
            ->selectRaw('MAX(id) as id')
            ->groupBy('package_id')
            ->pluck('id');

        $circleds = Member::whereIn('id', $memberIds)
            ->where('user_id',$this->id)
            ->orderBy('id', 'desc')
            ->pluck('circle_id');
        
        // Get circles where user is a member (existing logic for 2, 3, 4 downline circles)
        // Exclude 5-member circles from this query - for 5-member circles, users should only see their own circles
        $circles = Circle::whereIn('id',$circleds)
            ->where('status','Active')
            ->whereHas('package', function($query) {
                $query->where('total_members', '!=', 5); // Exclude 5-member circles
            })
            ->with(['package','members'])
            ->get();
        
        // For 5-member circles: ONLY include circles where user is the owner (position 5)
        // Users should NOT see circles where they are just filling positions 1-4
        $user_5_member_circles = Circle::where('user_id', $this->id)
            ->whereHas('package', function($query) {
                $query->where('total_members', 5);
            })
            ->where('status', 'Active')
            ->with(['package','members'])
            ->get();
        
        // Merge and return unique circles
        return $circles->merge($user_5_member_circles)->unique('id');
    }
    
    // public function getActiveCirclesAttribute()
    // {
    //     $members = Member::where('user_id', $this->id)
    //         ->whereHas('circle.package') // Ensure the member has a circle with a package
    //         ->orderBy('updated_at', 'desc') // Get the latest members first
    //         ->get()
    //         ->unique(function ($member) {
    //             return $member->circle->package->id; // Ensure unique package IDs
    //         });
            
    //     foreach($members as $member){
    //         if($member->circle->user_id != $this->id){
    //             $circle = Circle::where('id',$member->circle_id)->with(['package', 'members'])->first();
    //             if ($circle && $circle->status == 'Active') {
    //                 $circles->prepend($circle);
    //             }
    //         }
    //     }
    //     return $circles;
    // }
    
    public function completed_circles()
    {
        return $this->hasMany('App\Models\Circle')->where('status','Completed')->orderBy('id','desc')->with(['package','members']);
    }
    
    public function circle_rewards($package_id)
    {
        return CircleReward::where('user_id', $this->id)
        ->whereHas('circle', function ($query) use ($package_id) {
            $query->where('package_id', $package_id);
        })
        ->get();
    }

    public function transactions()
    {
        return $this->hasMany('App\Models\Transaction');
    }
    
    public function trips()
    {
        return $this->hasMany('\App\Models\Trip');
    }
    
    public function create_circle($package_id)
    {
        $package = Package::find($package_id);
        $name = $this->generateUniqueString(8);
        $circle = new Circle();
        $circle->user_id = $this->id;
        $circle->name = $name;
        $circle->package_id = $package->id;
        $circle->reward_amount = 0;
        $circle->status = 'Active';
        $circle->save();
        $this->create_circle_members($circle->id);
        
        return $this->circles;
    }
    
    public function create_circle_members($circle_id)
    {
        $circle = Circle::with('package')->find($circle_id);
        
        // Check if this is a 5-member circle
        if($circle->package->total_members == 5){
            $this->create_5_member_circle_members($circle_id);
            return;
        }
        
        // Existing logic for 2, 3, 4 downline circles - DO NOT CHANGE
        $member = new Member();
        $member->circle_id = $circle->id;
        $member->user_id = $this->id;
        $member->position = 1;
        $member->status = 'Occupied';
        $member->package_id = $circle->package_id;
        $member->save();
        for ($i = 2; $i <= $circle->package->total_members; $i++){
            $member = new Member();
            $member->circle_id = $circle->id;
            $member->position = $i;
            $member->package_id = $circle->package_id;
            $member->save();
        }
    }
    
    /**
     * Create 5-member circle members
     * Positions 1-4 are empty, position 5 is occupied by the circle owner
     */
    public function create_5_member_circle_members($circle_id)
    {
        $circle = Circle::with('package')->find($circle_id);
        
        // Create positions 1-4 as empty
        for ($i = 1; $i <= 4; $i++){
            $member = new Member();
            $member->circle_id = $circle->id;
            $member->position = $i;
            $member->status = 'Empty';
            $member->package_id = $circle->package_id;
            $member->save();
        }
        
        // Position 5 is occupied by the circle owner
        $member = new Member();
        $member->circle_id = $circle->id;
        $member->user_id = $this->id;
        $member->position = 5;
        $member->status = 'Occupied';
        $member->package_id = $circle->package_id;
        $member->save();
    }
    
    /**
     * Create 5-member circle (simple circle)
     */
    public function create_5_member_circle($package_id)
    {
        $package = Package::find($package_id);
        if($package->total_members != 5){
            return false; // Not a 5-member circle
        }
        
        $name = $this->generateUniqueString(8);
        $circle = new Circle();
        $circle->user_id = $this->id;
        $circle->name = $name;
        $circle->package_id = $package->id;
        $circle->reward_amount = 0;
        $circle->status = 'Active';
        $circle->save();
        $this->create_5_member_circle_members($circle->id);
        
        return $circle;
    }
    
    public function update_circle_member($circle_id,$user_id)
    {
        // Retrieve the package
        $package = Package::find($package_id);
        if (!$package) {
            return response()->json(['error' => 'Package not found'], 404);
        }
    
        if (!$referal) {
            return false;
        }
        $current_user = User::find($user_id);
        
        $circle = Circle::find($circle_id);
        if (!$circle) {
            return false;
        }
        $package = $circle->package;
        $referal = $circle->user;
        Log::info("Updating circle for user- ".$referal->username." package purchased by ".$current_user->username);
        
        $max_downlines = $package->max_downlines;
        $main_member = Member::where('circle_id', $circle->id)->where('user_id',$referal->id)->first();
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
            // if 1st package
            $first_circle = Circle::where('package_id',$package->id)->first();
            // if($first_circle->id == $circle->id){
            //     if($occupied_count == 1){
            //         $first_position->user_id = $user_id;
            //         $first_position->status = 'Occupied';
            //         $first_position->save();
                    
            //         $third_position->user_id = $referal->id;
            //         $third_position->status = 'Occupied';
            //         $third_position->save();
                    
            //     }
            //     if($occupied_count == 2){
            //         $second_position->user_id = $user_id;
            //         $second_position->status = 'Occupied';
            //         $second_position->save();
            //     }
                
            //     if($occupied_count == 3){
            //         $seven_position->user_id = $third_position->user_id;
            //         $seven_position->status = 'Occupied';
            //         $seven_position->save();
                    
            //         $third_position->user_id = $first_position->user_id;
            //         $third_position->status = 'Occupied';
            //         $third_position->save();
                    
            //         $first_position->user_id = $user_id;
            //         $first_position->status = 'Occupied';
            //         $first_position->save();
                    
            //     }
                
            //     if($occupied_count == 4){
                    
            //         $six_position->user_id = $second_position->user_id;
            //         $six_position->status = 'Occupied';
            //         $six_position->save();
                    
            //         $second_position->user_id = $user_id;
            //         $second_position->status = 'Occupied';
            //         $second_position->save();
                    
            //     }
                
            //     if ($occupied_count > 4 && $occupied_count < 7) {
            //         if ($current_user->referal_id == $six_position->user_id || $current_user->referal_id == $fifth_position->user_id) {
            //             $this->assignPosition([$fifth_position, $fourth_position], $user_id);
            //         } elseif ($current_user->referal_id == $third_position->user_id || $current_user->referal_id == $first_position->user_id) {
            //             $this->assignPosition([$first_position, $second_position], $user_id);
            //         } elseif ($current_user->referal_id == $seven_position->user_id) {
            //             $this->assignPosition([$first_position, $second_position, $fifth_position,$fourth_position], $user_id);
            //         } else {
            //             //
            //         }
            //     }
                
            //     // if($occupied_count == 5){
            //     //     $fifth_position->user_id = $user_id;
            //     //     $fifth_position->status = 'Occupied';
            //     //     $fifth_position->save();
                    
            //     // }
                
            //     // if($occupied_count == 6){
            //     //     $fourth_position->user_id = $user_id;
            //     //     $fourth_position->status = 'Occupied';
            //     //     $fourth_position->save();
                    
            //     // }
            // }else{
                if($occupied_count == 1){
                    $third_position->user_id = $first_position->user_id;
                    $third_position->status = 'Occupied';
                    $third_position->save();
                    
                    $first_position->user_id = $user_id;
                    $first_position->status = 'Occupied';
                    $first_position->save();
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
                }
                if ($occupied_count > 2 && $occupied_count < 7) {
                    if ($current_user->referal_id == $six_position->user_id || $current_user->referal_id == $fifth_position->user_id) {
                        Log::info("current-user is ". $current_user .", in section 2 of referal - ".$referal->username);
                        $this->assignPosition([$fifth_position, $fourth_position], $user_id);
                    } elseif ($current_user->referal_id == $third_position->user_id || $current_user->referal_id == $first_position->user_id) {
                        Log::info("current-user is ". $current_user .", in section 1 of referal - ".$referal->username);
                        $this->assignPosition([$first_position, $second_position], $user_id);
                    } elseif ($current_user->referal_id == $seven_position->user_id) {
                        Log::info("current-user is ". $current_user .", in section any of referal - ".$referal->username);
                        $this->assignPosition([$first_position, $second_position, $fifth_position,$fourth_position], $user_id);
                    } else {
                        //
                    }
                }
            // }
            
            
            // check for reward
            // check for 1st section
                
                if($first_position->status == 'Occupied' && $second_position->status == 'Occupied' && $third_position->status == 'Occupied' && $seven_position->status == 'Occupied'){
                    // check if already given reward
                    $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',1)->first();
                    if(!$circle_reward){
                        $downlines = $referal->downlines;
                        $purchsed_packages_count = 0;
                        foreach($downlines as $downline){
                            $downline_circle = Circle::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                            if($downline_circle){
                                $purchsed_packages_count = $purchsed_packages_count + 1;
                            }
                            if($purchsed_packages_count >= 2){
                                break;
                            }
                        }
                        if($purchsed_packages_count >= 2){
                            // add reward to referal
                            $trip = new Trip();
                            $trip->user_id = $referal->id;
                            $trip->save();
                            
                            $circle_reward = new CircleReward();
                            $referal_circle = Circle::where('user_id',$referal->id)->where('package_id',$package->id)->where('status','Active')->first();
                            $circle_reward->user_id = $referal->id;
                            $circle_reward->circle_id = $referal_circle->id;
                            $circle_reward->trip_id = $trip->id;
                            $circle_reward->amount = $package->reward_amount;
                            $circle_reward->section = 1;
                            $circle_reward->desc = '1st Section completed';
                            $circle_reward->status = 'Success';
                            $circle_reward->save();
                            
                            $referal->wallet = $referal->wallet + $package->reward_amount;
                            $referal->save();
                            
                            $this->create_transaction($referal->id, 'Credit', $package->reward_amount, $referal->wallet, '1st Section completed');
                        }
                    }
                }
                
            // check for 2nd section
                
                if($fourth_position->status == 'Occupied' && $fifth_position->status == 'Occupied' && $six_position->status == 'Occupied' && $seven_position->status == 'Occupied'){
                    // check if already given reward
                    $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',2)->first();
                        if(!$circle_reward){
                            $downlines = $referal->downlines;
                        $purchsed_packages_count = 0;
                        foreach($downlines as $downline){
                            $downline_circle = Circle::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                            if($downline_circle){
                                $purchsed_packages_count = $purchsed_packages_count + 1;
                            }
                            if($purchsed_packages_count >= 2){
                                break;
                            }
                        }
                        if($purchsed_packages_count >= 2){
                            // add reward to referal
                            $trip = new Trip();
                            $trip->user_id = $referal->id;
                            $trip->save();
                            
                            $circle_reward = new CircleReward();
                            $referal_circle = Circle::where('user_id',$referal->id)->where('package_id',$package->id)->where('status','Active')->first();
                            $circle_reward->user_id = $referal->id;
                            $circle_reward->circle_id = $referal_circle->id;
                            $circle_reward->trip_id = $trip->id;
                            $circle_reward->amount = $package->reward_amount;
                            $circle_reward->section = 2;
                            $circle_reward->desc = '2nd Section completed';
                            $circle_reward->status = 'Success';
                            $circle_reward->save();
                            
                            $referal->wallet = $referal->wallet + $package->reward_amount;
                            $referal->save();
                            $this->create_transaction($referal->id, 'Credit', $package->reward_amount, $referal->wallet, '2nd Section completed');
                        }
                    }
                }
        }
        if($jump_position == 13){
            if($occupied_count == 1){
                $first_position->user_id = $user_id;
                $first_position->status = 'Occupied';
                $first_position->save();
                
                $fourth_position->user_id = $referal->id;
                $fourth_position->status = 'Occupied';
                $fourth_position->save();
                
            }
            if($occupied_count == 2){
                $second_position->user_id = $user_id;
                $second_position->status = 'Occupied';
                $second_position->save();
                
            }
            if($occupied_count == 3){
                $thirteen_position->user_id = $referal->id;
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
            }
            
            if ($occupied_count > 3 && $occupied_count < 13) {
                if ($current_user->referal_id == $twelve_position->user_id) {
                    $this->assignPosition([$eleven_position, $ten_position, $nine_position, $first_position, $second_position,$third_position, $fifth_position, $six_position,$seven_position], $user_id);
                } elseif ($current_user->referal_id == $eight_position->user_id) {
                    $this->assignPosition([$fifth_position, $six_position,$seven_position, $first_position, $second_position,$third_position, $eleven_position, $ten_position, $nine_position], $user_id);
                } elseif ($current_user->referal_id == $fourth_position->user_id) {
                    $this->assignPosition([$first_position, $second_position,$third_position, $fifth_position, $six_position,$seven_position,$eleven_position, $ten_position,$nine_position], $user_id);
                } else {
                    $this->assignPosition([$first_position, $second_position,$third_position, $fifth_position, $six_position,$seven_position,$eleven_position, $ten_position,$nine_position], $user_id);
                }
            }
            
            // check for reward
            // check for 1st section
                
                if($first_position->status == 'Occupied' && $second_position->status == 'Occupied' && $third_position->status == 'Occupied' && $fourth_position->status == 'Occupied' && $thirteen_position->status == 'Occupied'){
                    // check if already given reward
                    $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',1)->first();
                        if(!$circle_reward){
                            $downlines = $referal->downlines;
                        $purchsed_packages_count = 0;
                        foreach($downlines as $downline){
                            $downline_circle = Circle::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                            if($downline_circle){
                                $purchsed_packages_count = $purchsed_packages_count + 1;
                            }
                            if($purchsed_packages_count >= 2){
                                break;
                            }
                        }
                        if($purchsed_packages_count >= 2){
                            // add reward to referal
                            $trip = new Trip();
                            $trip->user_id = $referal->id;
                            $trip->save();
                            
                            $circle_reward = new CircleReward();
                            $referal_circle = Circle::where('user_id',$referal->id)->where('package_id',$package->id)->where('status','Active')->first();
                            $circle_reward->user_id = $referal->id;
                            $circle_reward->circle_id = $referal_circle->id;
                            $circle_reward->trip_id = $trip->id;
                            $circle_reward->amount = $package->reward_amount;
                            $circle_reward->section = 1;
                            $circle_reward->desc = '1st Section completed';
                            $circle_reward->status = 'Success';
                            $circle_reward->save();
                            
                            $referal->wallet = $referal->wallet + $package->reward_amount;
                            $referal->save();
                            
                            $this->create_transaction($referal->id, 'Credit', $package->reward_amount, $referal->wallet, '1st Section completed');
                        }
                    }
                }
                
            // check for 2nd section
                
                if($fifth_position->status == 'Occupied' && $six_position->status == 'Occupied' && $seven_position->status == 'Occupied' && $eight_position->status == 'Occupied' && $thirteen_position->status == 'Occupied'){
                    // check if already given reward
                    $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',2)->first();
                        if(!$circle_reward){
                            $downlines = $referal->downlines;
                        $purchsed_packages_count = 0;
                        foreach($downlines as $downline){
                            $downline_circle = Circle::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                            if($downline_circle){
                                $purchsed_packages_count = $purchsed_packages_count + 1;
                            }
                            if($purchsed_packages_count >= 2){
                                break;
                            }
                        }
                        if($purchsed_packages_count >= 2){
                            // add reward to referal
                            $trip = new Trip();
                            $trip->user_id = $referal->id;
                            $trip->save();
                            
                            $circle_reward = new CircleReward();
                            $referal_circle = Circle::where('user_id',$referal->id)->where('package_id',$package->id)->where('status','Active')->first();
                            $circle_reward->user_id = $referal->id;
                            $circle_reward->circle_id = $referal_circle->id;
                            $circle_reward->trip_id = $trip->id;
                            $circle_reward->amount = $package->reward_amount;
                            $circle_reward->section = 2;
                            $circle_reward->desc = '2nd Section completed';
                            $circle_reward->status = 'Success';
                            $circle_reward->save();
                            
                            $referal->wallet = $referal->wallet + $package->reward_amount;
                            $referal->save();
                            $this->create_transaction($referal->id, 'Credit', $package->reward_amount, $referal->wallet, '2nd Section completed');
                        }
                    }
                }
                
            // check for 3rd section
                
                if($nine_position->status == 'Occupied' && $ten_position->status == 'Occupied' && $eleven_position->status == 'Occupied' && $twelve_position->status == 'Occupied' && $thirteen_position->status == 'Occupied'){
                    // check if already given reward
                    $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',3)->first();
                        if(!$circle_reward){
                            $downlines = $referal->downlines;
                        $purchsed_packages_count = 0;
                        foreach($downlines as $downline){
                            $downline_circle = Circle::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                            if($downline_circle){
                                $purchsed_packages_count = $purchsed_packages_count + 1;
                            }
                            if($purchsed_packages_count >= 2){
                                break;
                            }
                        }
                        if($purchsed_packages_count >= 2){
                            // add reward to referal
                            $trip = new Trip();
                            $trip->user_id = $referal->id;
                            $trip->save();
                            
                            $circle_reward = new CircleReward();
                            $referal_circle = Circle::where('user_id',$referal->id)->where('package_id',$package->id)->where('status','Active')->first();
                            $circle_reward->user_id = $referal->id;
                            $circle_reward->circle_id = $referal_circle->id;
                            $circle_reward->trip_id = $trip->id;
                            $circle_reward->amount = $package->reward_amount;
                            $circle_reward->section = 3;
                            $circle_reward->desc = '3rd Section completed';
                            $circle_reward->status = 'Success';
                            $circle_reward->save();
                            
                            $referal->wallet = $referal->wallet + $package->reward_amount;
                            $referal->save();
                            $this->create_transaction($referal->id, 'Credit', $package->reward_amount, $referal->wallet, '3rd Section completed');
                        }
                    }
                }
        }
        
        if($jump_position == 21){
            if($occupied_count == 1){
                $first_position->user_id = $user_id;
                $first_position->status = 'Occupied';
                $first_position->save();
                
                $fifth_position->user_id = $referal->id;
                $fifth_position->status = 'Occupied';
                $fifth_position->save();
                
            }
            if($occupied_count == 2){
                $second_position->user_id = $user_id;
                $second_position->status = 'Occupied';
                $second_position->save();
            }
            if($occupied_count == 3){
                $third_position->user_id = $user_id;
                $third_position->status = 'Occupied';
                $third_position->save();
            }
            if($occupied_count == 4){
                $twenty_one_position->user_id = $referal->id;
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
                
                $twenty_position->user_id = $user_id;
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
            }
            
            if ($occupied_count > 4 && $occupied_count < 21) {
                if ($current_user->referal_id == $twenty_position->user_id) {
                    $this->assignPosition([$nineteen_position, $eighteen_position, $seventeen_position,$sixteen_position,$fourteen_position, $thirteen_position,$twelve_position,$eleven_position,
                    $first_position, $second_position,$third_position,$fourth_position,$six_position,$seven_position,$eight_position,$nine_position], $user_id);
                } elseif ($current_user->referal_id == $fifteen_position->user_id) {
                    $this->assignPosition([$fourteen_position, $thirteen_position,$twelve_position,$eleven_position, $nineteen_position, $eighteen_position, $seventeen_position,$sixteen_position,
                    $first_position, $second_position,$third_position,$fourth_position,$six_position,$seven_position,$eight_position,$nine_position], $user_id);
                } elseif ($current_user->referal_id == $ten_position->user_id) {
                    $this->assignPosition([$six_position, $seven_position,$eight_position,$nine_position,$first_position, $second_position,$third_position,$fourth_position,
                    $nineteen_position, $eighteen_position, $seventeen_position,$sixteen_position,$fourteen_position, $thirteen_position], $user_id);
                } elseif ($current_user->referal_id == $fifth_position->user_id) {
                    $this->assignPosition([$first_position, $second_position,$third_position,$fourth_position,$six_position, $seven_position,$eight_position,$nine_position,$first_position,
                    $nineteen_position, $eighteen_position, $seventeen_position,$sixteen_position,$fourteen_position, $thirteen_position,$twelve_position,$eleven_position], $user_id);
                } else {
                    $this->assignPosition([$first_position, $second_position,$third_position,$fourth_position,$six_position,$seven_position,$eight_position,$nine_position,
                    $nineteen_position, $eighteen_position, $seventeen_position,$sixteen_position,$fourteen_position, $thirteen_position,$twelve_position,$eleven_position], $user_id);
                }
            }
            
            // check for reward
            // check for 1st section
                
                if($first_position->status == 'Occupied' && $second_position->status == 'Occupied' && $third_position->status == 'Occupied' && $fourth_position->status == 'Occupied' && $fifth_position->status == 'Occupied' && $twenty_one_position->status == 'Occupied'){
                    // check if already given reward
                    $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',1)->first();
                        if(!$circle_reward){
                            $downlines = $referal->downlines;
                        $purchsed_packages_count = 0;
                        foreach($downlines as $downline){
                            $downline_circle = Circle::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                            if($downline_circle){
                                $purchsed_packages_count = $purchsed_packages_count + 1;
                            }
                            if($purchsed_packages_count >= 2){
                                break;
                            }
                        }
                        if($purchsed_packages_count >= 2){
                            // add reward to referal
                            $trip = new Trip();
                            $trip->user_id = $referal->id;
                            $trip->save();
                            
                            $circle_reward = new CircleReward();
                            $referal_circle = Circle::where('user_id',$referal->id)->where('package_id',$package->id)->where('status','Active')->first();
                            $circle_reward->user_id = $referal->id;
                            $circle_reward->circle_id = $referal_circle->id;
                            $circle_reward->trip_id = $trip->id;
                            $circle_reward->amount = $package->reward_amount;
                            $circle_reward->section = 1;
                            $circle_reward->desc = '1st Section completed';
                            $circle_reward->status = 'Success';
                            $circle_reward->save();
                            
                            $referal->wallet = $referal->wallet + $package->reward_amount;
                            $referal->save();
                            
                            $this->create_transaction($referal->id, 'Credit', $package->reward_amount, $referal->wallet, '1st Section completed');
                        }
                    }
                }
                
            // check for 2nd section
                
                if($six_position->status == 'Occupied' && $seven_position->status == 'Occupied' && $eight_position->status == 'Occupied' && $nine_position->status == 'Occupied' && $ten_position->status == 'Occupied' &&  $twenty_one_position->status == 'Occupied'){
                    // check if already given reward
                    $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',2)->first();
                        if(!$circle_reward){
                            $downlines = $referal->downlines;
                        $purchsed_packages_count = 0;
                        foreach($downlines as $downline){
                            $downline_circle = Circle::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                            if($downline_circle){
                                $purchsed_packages_count = $purchsed_packages_count + 1;
                            }
                            if($purchsed_packages_count >= 2){
                                break;
                            }
                        }
                        if($purchsed_packages_count >= 2){
                            // add reward to referal
                            $trip = new Trip();
                            $trip->user_id = $referal->id;
                            $trip->save();
                            
                            $circle_reward = new CircleReward();
                            $referal_circle = Circle::where('user_id',$referal->id)->where('package_id',$package->id)->where('status','Active')->first();
                            $circle_reward->user_id = $referal->id;
                            $circle_reward->circle_id = $referal_circle->id;
                            $circle_reward->trip_id = $trip->id;
                            $circle_reward->amount = $package->reward_amount;
                            $circle_reward->section = 2;
                            $circle_reward->desc = '2nd Section completed';
                            $circle_reward->status = 'Success';
                            $circle_reward->save();
                            
                            $referal->wallet = $referal->wallet + $package->reward_amount;
                            $referal->save();
                            $this->create_transaction($referal->id, 'Credit', $package->reward_amount, $referal->wallet, '2nd Section completed');
                        }
                    }
                }
                
            // check for 3rd section
                
                if($eleven_position->status == 'Occupied' && $twelve_position->status == 'Occupied' && $thirteen_position->status == 'Occupied' && $fourteen_position->status == 'Occupied' && $fifteen_position->status == 'Occupied' && $twenty_one_position->status == 'Occupied'){
                    // check if already given reward
                    $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',3)->first();
                        if(!$circle_reward){
                            $downlines = $referal->downlines;
                        $purchsed_packages_count = 0;
                        foreach($downlines as $downline){
                            $downline_circle = Circle::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                            if($downline_circle){
                                $purchsed_packages_count = $purchsed_packages_count + 1;
                            }
                            if($purchsed_packages_count >= 2){
                                break;
                            }
                        }
                        if($purchsed_packages_count >= 2){
                            // add reward to referal
                            $trip = new Trip();
                            $trip->user_id = $referal->id;
                            $trip->save();
                            
                            $circle_reward = new CircleReward();
                            $referal_circle = Circle::where('user_id',$referal->id)->where('package_id',$package->id)->where('status','Active')->first();
                            $circle_reward->user_id = $referal->id;
                            $circle_reward->circle_id = $referal_circle->id;
                            $circle_reward->trip_id = $trip->id;
                            $circle_reward->amount = $package->reward_amount;
                            $circle_reward->section = 3;
                            $circle_reward->desc = '3rd Section completed';
                            $circle_reward->status = 'Success';
                            $circle_reward->save();
                            
                            $referal->wallet = $referal->wallet + $package->reward_amount;
                            $referal->save();
                            $this->create_transaction($referal->id, 'Credit', $package->reward_amount, $referal->wallet, '3rd Section completed');
                        }
                    }
                }
                
                // check for 4th section
                
                if($sixteen_position->status == 'Occupied' && $seventeen_position->status == 'Occupied' && $eighteen_position->status == 'Occupied' && $nineteen_position->status == 'Occupied' && $twenty_position->status == 'Occupied' && $twenty_one_position->status == 'Occupied'){
                    // check if already given reward
                    $circle_reward = CircleReward::where('circle_id',$circle->id)->where('section',4)->first();
                        if(!$circle_reward){
                            $downlines = $referal->downlines;
                        $purchsed_packages_count = 0;
                        foreach($downlines as $downline){
                            $downline_circle = Circle::where('package_id',$package->id)->where('user_id',$downline->id)->first();
                            if($downline_circle){
                                $purchsed_packages_count = $purchsed_packages_count + 1;
                            }
                            if($purchsed_packages_count >= 2){
                                break;
                            }
                        }
                        if($purchsed_packages_count >= 2){
                            // add reward to referal
                            $trip = new Trip();
                            $trip->user_id = $referal->id;
                            $trip->save();
                            
                            $circle_reward = new CircleReward();
                            $referal_circle = Circle::where('user_id',$referal->id)->where('package_id',$package->id)->where('status','Active')->first();
                            $circle_reward->user_id = $referal->id;
                            $circle_reward->circle_id = $referal_circle->id;
                            $circle_reward->trip_id = $trip->id;
                            $circle_reward->amount = $package->reward_amount;
                            $circle_reward->section = 4;
                            $circle_reward->desc = '4th Section completed';
                            $circle_reward->status = 'Success';
                            $circle_reward->save();
                            
                            $referal->wallet = $referal->wallet + $package->reward_amount;
                            $referal->save();
                            $this->create_transaction($referal->id, 'Credit', $package->reward_amount, $referal->wallet, '4th Section completed');
                        }
                    }
                }
        }
        
        $occupied_count = Member::where('circle_id', $circle->id)->where('status', 'Occupied')->count();
        if ($occupied_count == $package->total_members) {
            $circle->status = 'Completed';
            $circle->save();
            
            $referal->create_circle($package->id);
            
            $referal->wallet = $referal->wallet - $package->reward_amount;
            $referal->save();
            $this->create_transaction($referal->id, 'Debit', $package->reward_amount, $referal->wallet, $package->name.' Package Purchased');
            
            $this->update_new_circle_member($package->id,$referal->id);
        }
        
        return response()->json(['message' => 'Circle members updated successfully']);
    }
    
    public function check_referal($user_id,$package_id){
        $user = User::find($user_id);
        $user->update_circle_member($package_id,Auth::User()->id);
        $referal = $user->referal;
        if($referal){
            $this->check_referal($referal->id,$package_id);
        }
        return true;
    }
    
    public function check_downline($user_id,$package_id){
        $user = User::find($user_id);
        $user->update_circle_member($package_id,Auth::User()->id);
        $downline = $user->downline;
        if($downline){
            $this->check_downline($downline->id,$package_id);
        }
        return true;
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
    
    public function create_new_circle($package_id,$user_id)
    {
        $package = Package::find($package_id);
        $name = $this->generateUniqueString(8);
        $circle = new Circle();
        $circle->user_id = $user_id;
        $circle->name = $name;
        $circle->package_id = $package_id;
        $circle->reward_amount = 0;
        $circle->status = 'Active';
        $circle->save();
        $this->create_new_circle_members($circle->id);
        
        return $this->circles;
    }
    
    public function create_new_circle_members($circle_id)
    {
        $circle = Circle::find($circle_id);
        for ($i = 1; $i <= $circle->package->total_members; $i++){
            $member = new Member();
            $member->circle_id = $circle->id;
            $member->position = $i;
            $member->package_id = $circle->package_id;
            $member->save();
        }
        $package = $circle->package;
        $member = Member::where('circle_id',$circle->id)->where('position',$package->total_members)->first();
        $member->user_id = $circle->user_id;
        $member->status = 'Occupied';
        $member->package_id = $circle->package_id;
        $member->save();
    }
    
    public function update_new_circle_member($package_id,$user_id)
    {
        // 1. first fill upline circle
        $user = User::find($user_id);
        $referal = $user->referal;
        if($referal){
            Log::info($user->username ." goes to referal - ".$referal->username." circle after comleting");
            $circle = Circle::where('package_id',$package_id)->where('user_id',$referal->id)->where('status','Active')->first();
            if($circle){
                $user->update_circle_member($package_id,$user_id);
                return;
            }
        }
        // 2. if upline circle is completed same time( or means not empty place) check for downlines circle
        $downlines = $user->downlines;
        foreach($downlines as $downline){
            Log::info($user->username ." goes to downline - ".$downline->username." circle after comleting");
            $circle = Circle::where('package_id',$package_id)->where('user_id',$downline->id)->where('status','Active')->first();
            if($circle){
                $user = User::where('referal_id',$downline->id)->first();
                $user->update_circle_member($package_id,$user_id);
                return;
            }
        }
        // 3. if downlines circles is not empty - check upline -> downlines circles
        if($referal){
            $downlines = $user->referal->downlines;
            foreach($downlines as $downline){
                Log::info($user->username ." goes to upline downline - ".$downline->username." circle after comleting");
                $circle = Circle::where('package_id',$package_id)->where('user_id',$downline->id)->where('status','Active')->first();
                if($circle){
                    $user = User::where('referal_id',$downline->id)->first();
                    $user->update_circle_member($package_id,$user_id);
                    return;
                }
            }
        }
    }
    
    
    public function downline()
    {
        return $this->hasOne('App\Models\User','referal_id');
    }

    public function downlines()
    {
        return $this->hasMany('App\Models\User','referal_id');
    }
    
    public function referal()
    {
        return $this->belongsTo('App\Models\User','referal_id');
    }
    
    public function withdraws()
    {
        return $this->hasMany('App\Models\Withdraw');
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
