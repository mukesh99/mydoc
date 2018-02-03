<?php

namespace App\Http\Controllers;

use App\Institute;
use App\Placement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlacementController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $userInstituteIds = Institute::whereUserId(Auth::user()->id)->pluck('id')->toArray();
        $placements = Placement::whereIn('institute_id', $userInstituteIds)->paginate(12);
        return view('placement.index', compact('placements'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $institutes = Institute::whereUserId(Auth::id())->get();
        return view('placement.create',compact('institutes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'institute_id' => 'required|exists:institutes,id|in:' . implode(',', Institute::whereUserId(Auth::user()->id)->pluck('id')->toArray()),
            'student_pic' => 'required',
            'student_pic.*' => 'mimes:jpeg,jpg,png',
        ]);
        for ($i = 0; $i < count($request->file('student_pic')); $i++) {
            $path = $request->student_pic[$i]->store('placements', 'public');
            Placement::create(['institute_id' => $request->get('institute_id'), 'student_pic' => $path]);
        }
        return redirect()->route('placement.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $userInstituteIds = Institute::whereUserId(Auth::user()->id)->pluck('id')->toArray();
        $placement = Placement::findOrFail($id);
        if(!in_array($placement->institute->id,$userInstituteIds)){
            abort(404);
        }
        $placement->delete();
        return redirect()->route('placement.index');
    }
}
