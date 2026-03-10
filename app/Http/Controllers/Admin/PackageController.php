<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Package;
use App\Models\Color;
use App\Models\User;
use App\Models\Timer;

class PackageController extends Controller
{

    public function index()
    {
        $packages = Package::orderBy('id','asc')->withTrashed()->get();
        return view('admin.package.index',compact('packages'));
    }


    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $is_combo = (bool)$request->is_combo;

        $rules = [
            'name' => 'required',
            'price' => 'required',
            'total_members' => 'required',
        ];

        // reward_amount is only required for non-combo packages
        if(!$is_combo) {
            $rules['reward_amount'] = 'required';
        }

        // max_downlines_2 is only required for non-5-member circles and non-combo packages
        if($request->total_members != 5 && !$is_combo) {
            $rules['max_downlines_2'] = 'required';
        }

        if($is_combo){
            // 5-Member Circle 1 (five_a) rewards
            $rules['combo_five_a_reward_direct'] = 'required|numeric|min:0';
            $rules['combo_five_a_reward_autofill'] = 'required|numeric|min:0';
            $rules['combo_five_a_autorenew_amount'] = 'required|numeric|min:0';
            // 5-Member Circle 2 (five_b) rewards
            $rules['combo_five_b_reward_direct'] = 'required|numeric|min:0';
            $rules['combo_five_b_reward_autofill'] = 'required|numeric|min:0';
            $rules['combo_five_b_autorenew_amount'] = 'required|numeric|min:0';
            // 5-Member Circle 3 (five_c) rewards
            $rules['combo_five_c_reward_direct'] = 'required|numeric|min:0';
            $rules['combo_five_c_reward_autofill'] = 'required|numeric|min:0';
            $rules['combo_five_c_autorenew_amount'] = 'required|numeric|min:0';
            // 21-Member circle rewards
            $rules['combo_twentyone_reward_amount'] = 'required|numeric|min:0';
            $rules['combo_twentyone_autorenew_amount'] = 'required|numeric|min:0';
        }
    
        $msg = 'Package Added';
        $package = new Package();
    
        if ($request->id) {
            $package = Package::withTrashed()->find($request->id);
            $msg = 'Package Updated';
        }
    
        $validation = \Validator::make($request->all(), $rules);
    
        if ($validation->fails()) {
            return redirect()->back()->with('error', $validation->errors()->first());
        }
        
        $package->name = $request->name;
        $package->price = $request->price;
        $package->is_combo = $is_combo;
        // For 5-member circles, max_downlines should be null
        $package->max_downlines = ($request->total_members == 5) ? null : $request->max_downlines_2;
        $package->total_members = $request->total_members;
        // reward_amount is only set for non-combo packages
        if(!$is_combo) {
            $package->reward_amount = $request->reward_amount;
        } else {
            $package->reward_amount = 0; // Set to 0 for combo packages
        }

        if($is_combo){
            // 5-Member Circle 1 (five_a) rewards
            $package->combo_five_a_reward_direct = $request->combo_five_a_reward_direct;
            $package->combo_five_a_reward_autofill = $request->combo_five_a_reward_autofill;
            $package->combo_five_a_autorenew_amount = $request->combo_five_a_autorenew_amount;
            // 5-Member Circle 2 (five_b) rewards
            $package->combo_five_b_reward_direct = $request->combo_five_b_reward_direct;
            $package->combo_five_b_reward_autofill = $request->combo_five_b_reward_autofill;
            $package->combo_five_b_autorenew_amount = $request->combo_five_b_autorenew_amount;
            // 5-Member Circle 3 (five_c) rewards
            $package->combo_five_c_reward_direct = $request->combo_five_c_reward_direct;
            $package->combo_five_c_reward_autofill = $request->combo_five_c_reward_autofill;
            $package->combo_five_c_autorenew_amount = $request->combo_five_c_autorenew_amount;
            // 21-Member circle rewards
            $package->combo_twentyone_reward_amount = $request->combo_twentyone_reward_amount;
            $package->combo_twentyone_autorenew_amount = $request->combo_twentyone_autorenew_amount;
            // Section names
            $package->combo_five_a_name = $request->combo_five_a_name ?? null;
            $package->combo_five_b_name = $request->combo_five_b_name ?? null;
            $package->combo_five_c_name = $request->combo_five_c_name ?? null;
            $package->combo_twentyone_name = $request->combo_twentyone_name ?? null;
        }
        $package->save();
        
        // Create colors: 36 colors for combo packages (5+5+5+21), otherwise use total_members
        $colors_count = $package->colors->count();
        if($colors_count == 0){
            $total_colors = $is_combo ? 36 : $package->total_members; // 36 = 5+5+5+21 for combo packages
            for($i = 0; $i < $total_colors; $i++){
                $color = new Color();
                $color->package_id = $package->id;
                $color->position = $i + 1;
                $color->color = '#ddd';
                $color->save();
            }
        }
        
        
        if (!$request->id) {
            $admin = User::first();
            
            // Check if this is a combo package
            if($package->is_combo) {
                $admin->create_combo_package_circles($package->id);
            } elseif($package->total_members == 5) {
                // Create 5-member circle
                $admin->create_5_member_circle($package->id);
            } else {
                // Create regular circle (2, 3, 4 members)
                $admin->create_circle($package->id);
            }

            $timer = new Timer();
            $timer->user_id = $admin->id;
            $timer->package_id = $package->id;
            $timer->started_at = now();
            $timer->save();
        }
    
        return redirect()->back()->with('success', $msg);
    }

    public function show($id)
    {
        $package = Package::where('id',$id)->withTrashed()->first();
        if(!$package){
            return redirect()->route('package.index');
        }
        $colors_count = $package->colors->count();
        if($colors_count == 0){
            // Create colors: 36 colors for combo packages (5+5+5+21), otherwise use total_members
            $total_colors = $package->is_combo ? 36 : $package->total_members; // 36 = 5+5+5+21 for combo packages
            for($i = 0; $i < $total_colors; $i++){
                $color = new Color();
                $color->package_id = $package->id;
                $color->position = $i + 1;
                $color->color = '#ddd';
                $color->save();
            }
        }
        $package = Package::where('id',$id)->withTrashed()->first();
        return view('admin.package.show',compact('package'));
    }
    
    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id, Request $request)
    {
        $package = Package::where('id',$id)->withTrashed()->first();
        if($request->action == 'delete'){
            $package->delete();
        }else{
            $package->restore();
        }
        return response()->json([
            'msg' => 'success'
        ],200);
    }
    
    public function update_color(Request $request)
    {
        $rules = [
            'colors' => 'required',
        ];
    
        $msg = 'Color Updated';

        if ($request->id) {
            $package = Package::withTrashed()->find($request->id);
            $msg = 'Package Updated';
        }
    
        $validation = \Validator::make($request->all(), $rules);
    
        if ($validation->fails()) {
            return redirect()->back()->with('error', $validation->errors()->first());
        }
        $package = Package::find($request->package_id);
        $i = 0;
        foreach($package->colors as $color){
            $i++;
            $color->color = $request->colors[$i - 1];
            $color->save();
        }
    
        return redirect()->back()->with('success', $msg);
    }
}
