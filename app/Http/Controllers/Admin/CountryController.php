<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;

class CountryController extends Controller
{

    public function index()
    {
        $countries = Country::orderBy('id','asc')->withTrashed()->get();
        return view('admin.country.index',compact('countries'));
    }


    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:countries,name',
            'code' => 'required|unique:countries,code',
        ];
    
        $msg = 'The Country added successfully.';
        $country = new Country();
    
        if ($request->id) {
            $country = Country::withTrashed()->find($request->id);
            $rules['name'] .= ',' . $country->id;
            $rules['code'] .= ',' . $country->id;
            $msg = 'The Country updated successfully.';
        }
    
        $validation = \Validator::make($request->all(), $rules);
    
        if ($validation->fails()) {
            return redirect()->back()->with('error', $validation->errors()->first());
        }
    
        $country->name = $request->name;
        $country->code = $request->code;
        $country->save();
    
        return redirect()->back()->with('success', $msg);
    }

    public function show($id)
    {
        $country = Country::where('id',$id)->withTrashed()->first();
        if(!$country){
            return redirect()->route('country.index');
        }
        return view('admin.country.show',compact('country'));
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
        $country = Country::where('id',$id)->withTrashed()->first();
        if($request->action == 'delete'){
            $country->delete();
        }else{
            $country->restore();
        }
        return response()->json([
            'msg' => 'success'
        ],200);
    }
}
