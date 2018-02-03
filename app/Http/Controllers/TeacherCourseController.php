<?php

namespace App\Http\Controllers;

use App\Teacher;
use App\TeacherCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

class TeacherCourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       if (!empty(Input::get('teacher_id'))) {
            $teachers = Teacher::with('teacher_coupons')->whereTeacherId(Input::get('teacher_id'))->paginate(10);
        } else {
            $teacherIds = Teacher::whereUserId(Auth::id())->pluck('id')->toArray();
            $teacher_courses = TeacherCourse::with('teacher_coupons')->whereIn('teacher_id',$teacherIds)->paginate(10);
        }
        return view('teacher_course.index', compact('teacher_courses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
         $teacher_id = null;
        if (!empty(Input::get('teacher_id'))) {
            $teacher_id = Input::get('teacher_id');
        }
        $teachers = Teacher::whereUserId(Auth::user()->id)->get();
        return view('teacher_course.create', compact('teachers', 'teacher_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       $this->validate($request, [
            'teacher_id' => 'exists:teachers,id|in:' . implode(',', Teacher::whereUserId(Auth::user()->id)->pluck('id')->toArray()),
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'description' => 'required|string|max:65535',
            'exp_time' => 'required',
            'exp_course' => 'required',
            'price' => 'required|between:0.00,99999999.00'
        ]);
        $teacher_course = TeacherCourse::whereTeacherId($request->get('teacher_id'))->whereName($request->get('name'))->first();
        if ($teacher_course) {
            Session::flash('msg', 'Course with that name already exists for the selected institute!');
            return redirect()->back();
        }
        TeacherCourse::create($request->all());
        Session::flash('msg', 'Course has been added. Now you can add coupons!');
        return redirect()->route('teacher_course.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $teacherIds = Teacher::whereUserId(Auth::id())->pluck('id')->toArray();
        $teacher_course = TeacherCourse::findOrFail($id);
        if (!in_array($teacher_course->teacher_id, $teacherIds) && !Auth::user()->hasRole('admin')) {
            abort(404);
        }
        return view('teacher_course.show', compact('teacher_course'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
       $teacherIds = Teacher::whereUserId(Auth::id())->pluck('id')->toArray();
        $teacher_course = TeacherCourse::findOrFail($id);
        if (!in_array($teacher_course->teacher_id, $teacherIds) && !Auth::user()->hasRole('admin')) {
            abort(404);
        }
        $teachers = Teacher::whereUserId(Auth::user()->id)->get();
        return view('teacher_course.edit', compact('teacher_course','teachers'));
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
        $teacherIds = Teacher::whereUserId(Auth::id())->pluck('id')->toArray();
        $teacher_course = TeacherCourse::findOrFail($id);
        if (!in_array($teacher_course->teacher_id, $teacherIds) && !Auth::user()->hasRole('admin')) {
            abort(404);
        }

        $this->validate($request, [
            'teacher_id' => 'required|exists:teachers,id|in:' . implode(',', Teacher::whereUserId(Auth::user()->id)->pluck('id')->toArray()),
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'description' => 'required|string|max:65535',
            'exp_time' => 'required',
            'exp_course' => 'required',
            'price' => 'required|between:0.00,99999999.00'
        ]);
        $teacher_course = TeacherCourse::findOrFail($id);
        $teacher_course->fill($request->all());
        $teacher_course->save();
        return redirect()->route('teacher_course.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $teacherIds = Teacher::whereUserId(Auth::id())->pluck('id')->toArray();
        $teacher_course = TeacherCourse::findOrFail($id);
        if (!in_array($teacher_course->teacher_id, $teacherIds) && !Auth::user()->hasRole('admin')) {
            abort(404);
        }
        $teacher_course = TeacherCourse::findOrFail($id);
        $teacher_course->delete();
        return redirect()->route('teacher_course.index');
    }
}
