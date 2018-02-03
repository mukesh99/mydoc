<?php

namespace App\Http\Controllers;

use App\Address;
use App\Country;
use App\Teacher;
use App\TeacherCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       $teacher = Teacher::whereUserId(Auth::user()->id)->first();

        if(!$teacher){
            return redirect()->route('teacher.create');
        }

        return view('teacher.index',compact('teacher'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $teacher = Teacher::whereUserId(Auth::user()->id)->first();

        $countries = Country::whereIsActive(true)->get();
        
        return view('home',compact('teacher','countries'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
     $this->validate($request,[
        'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
        'email' => ['required','email','string','max:255',Rule::unique('users')->ignore(Auth::user()->id)],
        'phone' => ['required','digits:10','regex:/^[789]\d{9}$/',Rule::unique('users')->ignore(Auth::user()->id)],
        'profile_pic' => 'required|image',
        'teacher_id' => 'exists:teachers,id|in:' . implode(',', Teacher::whereUserId(Auth::user()->id)->pluck('id')->toArray()),
        'line1' => 'required|string|max:255',
        'line2' => 'sometimes|string|max:255',
        'country_id' => 'required|exists:countries,id',
        'state_id' => 'required|exists:states,id',
        'city_id' => 'required|exists:cities,id',
        'area_id' => 'required|exists:areas,id',
        'zip_code' => 'nullable|digits:6',
    ]);
     if ($request->file('profile_pic')->isValid() || $request->file('resume')->isValid()) {
        $path = $request->profile_pic->store('avatars', 'public');
        $teachers_path  = $request->resume->store('teachers_resume','public');
        Auth::user()->fill($request->all());
        Auth::user()->save();
        $address = Address::create($request->all());
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;
        $data['address_id'] = $address->id;
        $data['profile_pic'] = $path;
        $data['resume'] = $teachers_path;
        $teacher = Teacher::whereUserId(Auth::user()->id)->first();

        if(!$teacher){
            Teacher::create($data);
        }else{
            $teacher->fill($data);
            $teacher->save();
        }



        return redirect()->route('teacher.index');
    }
}


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $userTeacherIds = Teacher::whereUserId(Auth::user()->id)->pluck('id')->toArray();
        $teacher = Teacher::findOrFail($id);
        return view('teacher.show', compact('teacher'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $countries = Country::whereIsActive(true)->get();
         $teacher = Teacher::whereUserId(Auth::user()->id)->first();
       return view('teacher.edit',compact('countries','teacher'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
     $this->validate($request,[
         'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
        'email' => ['required','email','string','max:255',Rule::unique('users')->ignore(Auth::user()->id)],
        'phone' => ['required','digits:10','regex:/^[789]\d{9}$/',Rule::unique('users')->ignore(Auth::user()->id)],
        'teacher_id' => 'exists:teachers,id|in:' . implode(',', Teacher::whereUserId(Auth::user()->id)->pluck('id')->toArray()),
        'line1' => 'required|string|max:255',
        'line2' => 'sometimes|string|max:255',
        'country_id' => 'required|exists:countries,id',
        'state_id' => 'required|exists:states,id',
        'city_id' => 'required|exists:cities,id',
        'area_id' => 'required|exists:areas,id',
        'zip_code' => 'nullable|digits:6',

    ]);    
     Auth::user()->fill($request->all());
     Auth::user()->save();
     $teacher = Teacher::findOrFail($id);
     $teacher->address->fill($request->all());
     $teacher->address->save();
     $path = $teacher->profile_pic == null ? null : $teacher->profile_pic;
   
     if($request->hasFile('profile_pic')){
        if($request->file('profile_pic')->isValid()){
            if($teacher->profile_pic != null){
                Storage::disk('public')->delete($teacher->profile_pic);
                $path = $request->profile_pic->store('avatars', 'public');
            }else{
                $path = $request->profile_pic->store('avatars', 'public');
            }
        }
    }
      $teachers_path = $teacher->resume == null ? null : $teacher->resume;
      if($request->hasFile('resume')){
        if($request->file('resume')->isValid()){
            if($teacher->resume != null){
                Storage::disk('public')->delete($teacher->resume);
                $teachers_path = $request->resume->store('teachers_resume', 'public');
            }else{
                $teachers_path = $request->resume->store('teachers_resume', 'public');
            }
        }
    }

    /*$student = Student::whereUserId(Auth::user()->id)->first();*/
    $data = $request->all();

    $data['profile_pic'] = $path;
    $data['resume'] = $teachers_path;
    $teacher->fill($data);
    $teacher->save();

    return redirect()->route('teacher.index');
}

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
