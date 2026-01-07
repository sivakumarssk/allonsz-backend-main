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
        $rules = [
            'name' => 'required',
            'price' => 'required',
            'total_members' => 'required',
            'reward_amount' => 'required',
        ];
        
        // max_downlines_2 is only required for non-5-member circles
        if($request->total_members != 5) {
            $rules['max_downlines_2'] = 'required';
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
        // For 5-member circles, max_downlines should be null
        $package->max_downlines = ($request->total_members == 5) ? null : $request->max_downlines_2;
        $package->total_members = $request->total_members;
        $package->reward_amount = $request->reward_amount;
        $package->save();
        
        $colors_count = $package->colors->count();
        if($colors_count == 0){
            for($i = 0; $i < $package->total_members; $i++){
                $color = new Color();
                $color->package_id = $package->id;
                $color->position = $i + 1;
                $color->color = '#ddd';
                $color->save();
            }
        }
        
        
        if (!$request->id) {
            $admin = User::first();
            
            // Check if this is a 5-member circle
            if($package->total_members == 5) {
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
            for($i = 0; $i < $package->total_members; $i++){
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
