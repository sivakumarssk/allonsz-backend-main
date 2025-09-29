<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reward;

class RewardController extends Controller
{

    public function index()
    {
        //
    }


    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $rules = [
            'package_id' => 'required|exists:packages,id',
            'title' => 'required|max:191',
            'amount' => 'required|numeric',
            'position' => 'required|numeric',
        ];
    
        $msg = 'Reward Added';
        $reward = new Reward();
    
        if ($request->id) {
            $reward = Reward::find($request->id);
            $msg = 'Reward Updated';
        }
    
        $validation = \Validator::make($request->all(), $rules);
    
        if ($validation->fails()) {
            return redirect()->back()->with('error', $validation->errors()->first());
        }
    
        $reward->package_id = $request->package_id;
        $reward->title = $request->title;
        $reward->amount = $request->amount;
        $reward->position = $request->position;
        $reward->save();
    
        return redirect()->back()->with('success', $msg);
    }

    public function show($id)
    {
        //
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
        $reward = Reward::where('id',$id)->first();
        $reward->delete();
        return response()->json([
            'msg' => 'success'
        ],200);
    }
}
