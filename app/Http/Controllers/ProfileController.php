<?php

namespace App\Http\Controllers;

use App\Address;
use App\Country;
use App\Profile;
use App\Traits\ApiResponse;
use App\Traits\SmsClient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $profile = Auth::user()->profile;
        if (!$profile) {
            return redirect()->route('profile.create');
        }
        return view('profile.index', compact('profile'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $countries = Country::whereIsActive(true)->get();
        return view('profile.create', compact('countries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ageMin = Carbon::now()->subYear(16);
        $ageMax = Carbon::now()->subYear(84);
        $this->validate($request, [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|digits:10|unique:users|regex:/^[789]\d{9}$/',
            'profile_pic' => 'required|image|size:1024',
            'alternate_number' => 'nullable|digits:10|regex:/^[789]\d{9}$/',
            'dob' => 'required|date|before:'.$ageMin.'|after:'.$ageMax,
            'about' => 'nullable|string|max:65535',
            'line1' => 'required|string|max:255',
            'line2' => 'sometimes|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'zip_code' => 'nullable|digits:6'
        ]);
        if ($request->file('profile_pic')->isValid()) {
            $path = $request->profile_pic->store('avatars', 'public');
            $address = Address::create($request->all());
            $data = $request->all();
            $data['user_id'] = Auth::user()->id;
            $data['profile_pic'] = $path;
            $data['address_id'] = $address->id;
            Profile::create($data);
            return redirect()->route('profile.index');
        }
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
        if(Auth::user()->profile->id != $id){
            abort(404);
        }
        $countries = Country::whereIsActive(true)->get();
        $profile = Profile::whereUserId(Auth::user()->id)->first();
        return view('profile.edit', compact('profile', 'countries'));
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
        if(Auth::user()->profile->id != $id){
            abort(404);
        }
        $ageMin = Carbon::now()->subYear(16);
        $ageMax = Carbon::now()->subYear(84);
        $this->validate($request, [
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => ['required','email','string','max:255',Rule::unique('users')->ignore(Auth::user()->id)],
            'phone' => ['required','digits:10','regex:/^[789]\d{9}$/',Rule::unique('users')->ignore(Auth::user()->id)],
            'profile_pic' => 'sometimes|image|max:1024',
            'alternate_number' => 'nullable|digits:10|regex:/^[789]\d{9}$/',
            'dob' => 'required|date|before:'.$ageMin.'|after:'.$ageMax,
            'about' => 'nullable|string|max:65535',
            'line1' => 'required|string|max:255',
            'line2' => 'sometimes|string|max:255',
            'country_id' => 'required|exists:countries,id',
            'state_id' => 'required|exists:states,id',
            'city_id' => 'required|exists:cities,id',
            'area_id' => 'required|exists:areas,id',
            'zip_code' => 'nullable|digits:6'
        ]);
        Auth::user()->fill($request->all());
        Auth::user()->save();
        $profile = Profile::findOrFail($id);
        $path = $profile->profile_pic == null ? null : $profile->profile_pic;
        if($request->hasFile('profile_pic')){
            if($request->file('profile_pic')->isValid()){
                if($profile->profile_pic != null){
                    Storage::disk('public')->delete($profile->profile_pic);
                    $path = $request->profile_pic->store('avatars', 'public');
                }else{
                    $path = $request->profile_pic->store('avatars', 'public');
                }
            }
        }
        if($profile->address_id != null){
            $address = $profile->address;
            $address->fill($request->all());
            $address->save();
        }else{
            $address = Address::create($request->all());
        }
        $data = $request->all();
        $data['address_id'] = $address->id;
        $data['profile_pic'] = $path;
        $profile->fill($data);
        $profile->save();
        return redirect()->route('profile.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
