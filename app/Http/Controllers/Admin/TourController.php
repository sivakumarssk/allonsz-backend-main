<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tour;

class TourController extends Controller
{

    public function index()
    {
        $tours = Tour::orderBy('id','asc')->withTrashed()->get();
        return view('admin.tour.index',compact('tours'));
    }


    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        $rules = [
            'type' => 'required',
            'name' => 'required',
            'place' => 'required',
            'area' => 'required',
            'price' => 'required',
            'desc' => 'required',
            'photo' => 'nullable|image',
        ];
    
        $msg = 'Tour Added';
        $tour = new Tour();
    
        if ($request->id) {
            $tour = Tour::withTrashed()->find($request->id);
            $msg = 'Tour Updated';
        }
    
        $validation = \Validator::make($request->all(), $rules);
    
        if ($validation->fails()) {
            return redirect()->back()->with('error', $validation->errors()->first());
        }
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
                $oldFilePath = public_path('/images/toures/'.$tour->photo_filename);
                if (file_exists($oldFilePath) && $tour->photo_filename != '') {
                    unlink($oldFilePath);
                }
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                $filename = substr(str_shuffle(str_repeat($pool, 5)), 0, 12) .'.'.$extension;
                $path = $file->move(public_path('/images/toures'), $filename);
                $tour->photo = $filename;
            }else{
                return redirect()->back()->with('error', 'Invalid file format, please upload valid image file');
            }
        }
        $tour->type = $request->type;
        $tour->name = $request->name;
        $tour->place = $request->place;
        $tour->area = $request->area;
        $tour->price = $request->price;
        $tour->desc = $request->desc;
        
        $tour->save();
    
        return redirect()->back()->with('success', $msg);
    }

    public function show($id)
    {
        $tour = Tour::where('id',$id)->withTrashed()->first();
        if(!$tour){
            return redirect()->route('tour.index');
        }
        return view('admin.tour.show',compact('tour'));
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
        $tour = Tour::where('id',$id)->withTrashed()->first();
        if($request->action == 'delete'){
            $tour->delete();
        }else{
            $tour->restore();
        }
        return response()->json([
            'msg' => 'success'
        ],200);
    }
}
