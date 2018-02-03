<?php

namespace App\Http\Controllers;

use App\Country;
use App\Student;
use App\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         $countries = Country::whereIsActive(true)->get();
        $student = Student::whereUserId(Auth::user()->id)->first();
       if($student){
        return view('home',compact('student'));
       }

        $teacher = Teacher::whereUserId(Auth::user()->id)->first();
 if (empty($teacher)) {
            return view('home',compact('teacher','teacher_course','countries'));
        } else {
            return view('teacher.index', compact('teacher','teacher_course','countries'));
        }
       /* return view('home',compact('student','teacher','teacher_course','countries'));*/
    }

    public function profile()
    {
        return view('profile');
    }
}
