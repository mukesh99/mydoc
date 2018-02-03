<?php

namespace App\Http\Controllers;

use App\Address;
use App\Country;
use App\Institute;
use App\InstitutePic;
use App\Traits\SmsClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class InstituteController extends Controller
{
    use SmsClient;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $institutes = Institute::whereUserId(Auth::id())->paginate(10);
        return view('institute.index', compact('institutes'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countries = Country::whereIsActive(true)->get();
        return view('institute.create', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $institute = Institute::whereUserId(Auth::user()->id)->whereName($request->get('name'))->first();
        if ($institute) {
            Session::flash('msg', 'Institute with that name already exists!');
            return redirect()->back()->withInput($request->input());
        }
        $this->validate($request, [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'contact_person' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|digits:10|regex:/^[789]\d{9}$/',
            'about' => 'required|max:65535',
            'rating' => 'required|digits:1',
            'line1' => 'required|string|max:255',
            'line2' => 'sometimes|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'zip_code' => 'nullable|digits:6',
            'institute_pics' => 'required',
            'institute_pics.*' => 'mimes:jpeg,jpg,png|max:1024',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|string|alpha_dash|max:15',
            'g_plus' => 'nullable|url'
        ]);
        if(count($request->file('institute_pics')) > 3){
            Session::flash('msg_img', 'Maximum of 3 images are allowed!');
            return redirect()->back()->withInput($request->input());
        }
        $address = Address::create($request->all());
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;
        $data['address_id'] = $address->id;
        $institute = Institute::create($data);
        for ($i = 0; $i < count($request->file('institute_pics')); $i++) {
            $path = $request->institute_pics[$i]->store('institutes', 'public');
            InstitutePic::create(['institute_id' => $institute->id, 'image' => $path]);
        }
        Session::flash('msg', 'Institute has been added. Now you can add courses!');
        return redirect()->route('institute.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $userInstituteIds = Institute::whereUserId(Auth::user()->id)->pluck('id')->toArray();
        if (!in_array($id, $userInstituteIds) && !Auth::user()->hasRole('admin')) {
            abort(404);
        }
        $institute = Institute::findOrFail($id);
        return view('institute.show', compact('institute'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $userInstituteIds = Institute::whereUserId(Auth::user()->id)->pluck('id')->toArray();
        if (!in_array($id, $userInstituteIds) && !Auth::user()->hasRole('admin')) {
            abort(404);
        }
        $countries = Country::whereIsActive(true)->get();
        $institute = Institute::findOrFail($id);
        return view('institute.edit', compact('institute', 'countries'));
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
        $userInstituteIds = Institute::whereUserId(Auth::user()->id)->pluck('id')->toArray();
        if (!in_array($id, $userInstituteIds) && !Auth::user()->hasRole('admin')) {
            abort(404);
        }
        $this->validate($request, [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'contact_person' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|string|email|max:255',
            'phone' => 'required|digits:10|regex:/^[789]\d{9}$/',
            'about' => 'required|max:65535',
            'rating' => 'required|digits:1',
            'line1' => 'required|string|max:255',
            'line2' => 'sometimes|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'zip_code' => 'nullable|digits:6',
            'institute_pics' => 'sometimes',
            'institute_pics.*' => 'mimes:jpeg,jpg,png|max:1024',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|string|alpha_dash|max:15',
            'g_plus' => 'nullable|url'
        ]);
        if(count($request->file('institute_pics')) > 3){
            Session::flash('msg_img', 'Maximum of 3 images are allowed!');
            return redirect()->back();
        }
        $institute = Institute::findOrFail($id);
        $institute->address->fill($request->all());
        $institute->address->save();
        $institute->fill($request->all());
        $institute->save();
        $institutePics = InstitutePic::whereInstituteId($id)->get();
        if(count($institutePics)){
            for ($i = 0; $i < count($request->file('institute_pics')); $i++) {
                Storage::disk('public')->delete($institutePics[$i]->image);
                $path = $request->institute_pics[$i]->store('institutes', 'public');
                $institutePics[$i]->fill(['image' => $path]);
                $institutePics[$i]->save();
            }
        }else{
            for ($i = 0; $i < count($request->file('institute_pics')); $i++) {
                $path = $request->institute_pics[$i]->store('institutes', 'public');
                InstitutePic::create(['institute_id' => $institute->id, 'image' => $path]);
            }
        }
        return redirect()->route('institute.index');
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
        if (!in_array($id, $userInstituteIds) && !Auth::user()->hasRole('admin')) {
            abort(404);
        }
        $institute = Institute::findOrFail($id);
        $institute->delete();
        return redirect()->route('institute.index');
    }
}
