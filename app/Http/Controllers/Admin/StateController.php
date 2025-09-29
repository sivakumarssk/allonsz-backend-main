<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\State;

class StateController extends Controller
{

    public function index()
    {
        $states = State::orderBy('id','asc')->withTrashed()->get();
        return view('admin.state.index',compact('states'));
    }


    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|unique:states,name',
            'country_id' => 'required',
        ];
    
        $msg = 'State added successfully';
        $state = new State();
    
        if ($request->id) {
            $state = State::withTrashed()->find($request->id);
            $rules['name'] .= ',' . $state->id;
            $msg = 'State updated successfully';
        }
    
        $validation = \Validator::make($request->all(), $rules);
    
        if ($validation->fails()) {
            return redirect()->back()->with('error', $validation->errors()->first());
        }
    
        $state->name = $request->name;
        $state->country_id = $request->country_id;
        $state->save();
    
        return redirect()->back()->with('success', $msg);
    }

    public function show($id)
    {
        $state = State::where('id',$id)->withTrashed()->first();
        if(!$state){
            return redirect()->route('state.index');
        }
        return view('admin.state.show',compact('state'));
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
        $state = State::where('id',$id)->withTrashed()->first();
        if($request->action == 'delete'){
            $state->delete();
        }else{
            $state->restore();
        }
        return response()->json([
            'msg' => 'success'
        ],200);
    }
}
