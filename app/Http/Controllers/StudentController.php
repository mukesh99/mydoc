<?php

namespace App\Http\Controllers;

use App\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $student = Student::whereUserId(Auth::user()->id)->first();
        if(!$student){
            return redirect()->route('student.create');
        }
        return view('student.index',compact('student'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $student = Student::whereUserId(Auth::user()->id)->first();
        return view('home',compact('student'));
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
            'line2' => 'required|max:255',
            'course_name' =>'max:255|regex:/^[a-zA-Z\s]+$/',
           

        ]);
         if ($request->file('profile_pic')->isValid() || $request->file('resume'->isValid())) {
            $path = $request->profile_pic->store('avatars', 'public');
            $resume_path = $request->resume->store('students_resume','public');
        Auth::user()->fill($request->all());
        Auth::user()->save();
        $data = $request->all();
        $data['user_id'] = Auth::user()->id;
        $data['profile_pic'] = $path;
        $data['resume'] = $resume_path;
        $student = Student::whereUserId(Auth::user()->id)->first();
        if(!$student){
            Student::create($data);
        }else{
            $student->fill($data);
            $student->save();
        }

        return redirect()->route('student.index');
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
            'line2' => 'required|max:255',
            'course_name' =>'max:255|regex:/^[a-zA-Z\s]+$/',
          
           
        ]);    
        Auth::user()->fill($request->all());
        Auth::user()->save();
        $student = Student::findOrFail($id);
        $path = $student->profile_pic == null ? null : $student->profile_pic;
 if($request->hasFile('profile_pic')){
            if($request->file('profile_pic')->isValid()){
                if($student->profile_pic != null){
                    Storage::disk('public')->delete($student->profile_pic);
                    $path = $request->profile_pic->store('avatars', 'public');
                }else{
                    $path = $request->profile_pic->store('avatars', 'public');
                }
            }
        }

        $resume_path = $student->resume == null ? null : $student->resume;
        if($request->hasFile('resume')){
            if($request->file('resume')->isValid()){
                if($student->resume != null){
                    Storage::disk('public')->delete($student->resume);
                    $resume_path =$request->resume->store('students_resume','public');
                }else{
                    $resume_path = $request->resume->store('students_resume','public');
                }
            }
        }



        /*$student = Student::whereUserId(Auth::user()->id)->first();*/
        $data = $request->all();

        $data['profile_pic'] = $path;
        $data['resume'] = $resume_path;
        $student->fill($data);
        $student->save();
        return redirect()->route('student.index');
    
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
