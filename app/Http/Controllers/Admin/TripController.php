<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Trip;
use App\Models\Tour;

class TripController extends Controller
{

    public function index()
    {
        $trips = Trip::all();
        $tours = Tour::all();
        return view('admin.trip.index',compact('trips','tours'));
    }


    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $trip = new Trip();
        $rules = [
            'tour' => 'required|exists:tours,id',
            'members' => 'required|numeric',
            'from_date' => 'required',
            'to_date' => 'required',
            'from_place' => 'required',
            'status' => 'required|in:Pending,Approved',
        ];
        if($request->id != ''){
            $msg = 'Trip Updated';
            $trip = Trip::find($request->id);
        }else{
            $msg = 'Trip Added';
            $trip->user_id = $request->user_id;
        }
        $validation = \Validator::make( $request->all(), $rules );
        if( $validation->fails() ) {
            return redirect()->back()->with('error',$validation->errors()->first());
        }
        $trip->tour_id = $request->tour;
        $trip->members = $request->members;
        $trip->from_date = $request->from_date;
        $trip->to_date = $request->to_date;
        $trip->from_place = $request->from_place;
        $trip->status = $request->status;
        $trip->save();

        return redirect()->back()->with('success',$msg);
    }

    public function show($id)
    {
        $trip = Trip::where('id',$id)->first();
        if(!$trip){
            return redirect()->route('trip.index');
        }
        return view('admin.trip.show',compact('trip'));
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
        $trip = Trip::where('id',$id)->withTrashed()->first();
        if($trip){
            $trip->delete();
        }
        return response()->json([
            'msg' => 'success'
        ],200);
    }
}
