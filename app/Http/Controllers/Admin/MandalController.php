<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mandal;

class MandalController extends Controller
{

    public function index()
    {
        $mandals = Mandal::orderBy('id','asc')->withTrashed()->get();
        return view('admin.mandal.index',compact('mandals'));
    }


    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'district_id' => 'required',
        ];
    
        $msg = 'Mandal added successfully';
        $mandal = new Mandal();
    
        if ($request->id) {
            $mandal = Mandal::withTrashed()->find($request->id);
            $msg = 'Mandal updated successfully';
        }
    
        $validation = \Validator::make($request->all(), $rules);
    
        if ($validation->fails()) {
            return redirect()->back()->with('error', $validation->errors()->first());
        }
    
        $mandal->name = $request->name;
        $mandal->district_id = $request->district_id;
        $mandal->save();
    
        return redirect()->back()->with('success', $msg);
    }

    public function show($id)
    {
        $mandal = Mandal::where('id',$id)->withTrashed()->first();
        if(!$mandal){
            return redirect()->route('mandal.index');
        }
        return view('admin.mandal.show',compact('mandal'));
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
        $mandal = Mandal::where('id',$id)->withTrashed()->first();
        if($request->action == 'delete'){
            $mandal->delete();
        }else{
            $mandal->restore();
        }
        return response()->json([
            'msg' => 'success'
        ],200);
    }
}
