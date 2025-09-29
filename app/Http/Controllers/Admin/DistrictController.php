<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\District;

class DistrictController extends Controller
{

    public function index()
    {
        $districts = District::orderBy('id','asc')->withTrashed()->get();
        return view('admin.district.index',compact('districts'));
    }


    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:districts,name',
            'state_id' => 'required',
        ];
    
        $msg = 'District added successfully';
        $district = new District();
    
        if ($request->id) {
            $district = District::withTrashed()->find($request->id);
            $rules['name'] .= ',' . $district->id;
            $msg = 'District updated successfully';
        }
    
        $validation = \Validator::make($request->all(), $rules);
    
        if ($validation->fails()) {
            return redirect()->back()->with('error', $validation->errors()->first());
        }
    
        $district->name = $request->name;
        $district->state_id = $request->state_id;
        $district->save();
    
        return redirect()->back()->with('success', $msg);
    }

    public function show($id)
    {
        $district = District::where('id',$id)->withTrashed()->first();
        if(!$district){
            return redirect()->route('district.index');
        }
        return view('admin.district.show',compact('district'));
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
        $district = District::where('id',$id)->withTrashed()->first();
        if($request->action == 'delete'){
            $district->delete();
        }else{
            $district->restore();
        }
        return response()->json([
            'msg' => 'success'
        ],200);
    }
}
