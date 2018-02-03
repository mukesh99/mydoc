<?php

namespace App\Http\Controllers;

use App\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvertiseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $advertisements = Advertisement::paginate(10);
        return view('advertise.index', compact('advertisements'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('advertise.create');
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
            'image' => 'required',
            'image.*' => 'mimes:jpg,jpeg,png',
            'position' => 'required|string',
            'expiry_date' => 'required|date_format:Y-m-d H:i:s|after:today'
        ]);
        for ($i = 0; $i < count($request->file('image')); $i++) {
            $path = $request->image[$i]->store('advertise', 'public');
            Advertisement::create(['position' => $request->get('position'), 'image' => $path, 'expiry_date' => $request->get('expiry_date')]);
        }
        return redirect()->route('advertise.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $advertisement = Advertisement::findOrFail($id);
        return view('advertise.show',compact('advertisement'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $advertisement = Advertisement::findOrFail($id);
        return view('advertise.edit',compact('advertisement'));
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
            'image' => 'sometimes|mimes:jpg,jpeg,png',
            'position' => 'required|string',
            'expiry_date' => 'required|date_format:Y-m-d H:i:s|after:today'
        ]);
        $advertisement = Advertisement::findOrFail($id);
        $path = $advertisement->image;
        if($request->hasFile('image')){
            if($request->file('image')->isValid()){
                Storage::disk('public')->delete($advertisement->image);
                $path = $request->image->store('advertise', 'public');
            }
        }
        $data = $request->all();
        $data['image'] = $path;
        $advertisement->fill($data);
        $advertisement->save();
        return redirect()->route('advertise.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $advertisement = Advertisement::findOrFail($id);
        $advertisement->delete();
        return redirect()->route('advertise.index');
    }
}
