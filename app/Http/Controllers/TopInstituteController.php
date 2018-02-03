<?php

namespace App\Http\Controllers;

use App\Institute;
use App\TopInstitute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TopInstituteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $tops = TopInstitute::paginate(10);
        return view('top.index', compact('tops'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $institutes = Institute::whereIsActive(true)->get();
        return view('top.create', compact('institutes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $top = TopInstitute::whereInstituteId($request->get('institute_id'))->first();
        if ($top) {
            Session::flash('msg', 'Top Institute with that name already exists!');
            return redirect()->back();
        }
        $this->validate($request, [
            'institute_id' => 'required|exists:institutes,id',
            'position' => 'required|integer'
        ]);
        TopInstitute::create($request->all());
        return redirect()->route('top.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $top = TopInstitute::findOrFail($id);
        return view('top.show',compact('top'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $top = TopInstitute::findOrFail($id);
        $institutes = Institute::whereIsActive(true)->get();
        return view('top.edit', compact('top', 'institutes'));
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
        $this->validate($request, [
            'institute_id' => 'required|exists:institutes,id',
            'position' => 'required|integer'
        ]);
        $top = TopInstitute::findOrFail($id);
        $top->fill($request->all());
        $top->save();
        return redirect()->route('top.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $top = TopInstitute::findOrFail($id);
        $top->delete();
        Session::flash('msg_delete', 'Institute has been removed from top 10 list!');
        return redirect()->route('top.index');
    }
}
