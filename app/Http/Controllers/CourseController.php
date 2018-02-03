<?php

namespace App\Http\Controllers;

use App\Course;
use App\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!empty(Input::get('institute_id'))) {
            $courses = Course::with('coupons')->whereInstituteId(Input::get('institute_id'))->paginate(10);
        } else {
            $instituteIds = Institute::whereUserId(Auth::id())->pluck('id')->toArray();
            $courses = Course::with('coupons')->whereIn('institute_id',$instituteIds)->paginate(10);
        }
        return view('course.index', compact('courses'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $institute_id = null;
        if (!empty(Input::get('institute_id'))) {
            $institute_id = Input::get('institute_id');
        }
        $institutes = Institute::whereUserId(Auth::user()->id)->get();
        return view('course.create', compact('institutes', 'institute_id'));
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
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'description' => 'required|string|max:65535',
            'start_date' => 'required|date|after:yesterday',
            'end_date' => 'required|date|after:start_date',
            'price' => 'required|between:0.00,99999999.00'
        ]);
        $course = Course::whereInstituteId($request->get('institute_id'))->whereName($request->get('name'))->first();
        if ($course) {
            Session::flash('msg', 'Course with that name already exists for the selected institute!');
            return redirect()->back();
        }
        Course::create($request->all());
        Session::flash('msg', 'Course has been added. Now you can add coupons!');
        return redirect()->route('course.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $instituteIds = Institute::whereUserId(Auth::id())->pluck('id')->toArray();
        $course = Course::findOrFail($id);
        if (!in_array($course->institute_id, $instituteIds) && !Auth::user()->hasRole('admin')) {
            abort(404);
        }
        return view('course.show', compact('course'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $instituteIds = Institute::whereUserId(Auth::id())->pluck('id')->toArray();
        $course = Course::findOrFail($id);
        if (!in_array($course->institute_id, $instituteIds) && !Auth::user()->hasRole('admin')) {
            abort(404);
        }
        $institutes = Institute::whereUserId(Auth::user()->id)->get();
        return view('course.edit', compact('course','institutes'));
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
        $instituteIds = Institute::whereUserId(Auth::id())->pluck('id')->toArray();
        $course = Course::findOrFail($id);
        if (!in_array($course->institute_id, $instituteIds) && !Auth::user()->hasRole('admin')) {
            abort(404);
        }

        $this->validate($request, [
            'institute_id' => 'required|exists:institutes,id|in:' . implode(',', Institute::whereUserId(Auth::user()->id)->pluck('id')->toArray()),
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'description' => 'required|string|max:65535',
            'start_date' => 'required|date|after:yesterday',
            'end_date' => 'required|date|after:start_date',
            'price' => 'required|between:0.00,99999999.00'
        ]);
        $course = Course::findOrFail($id);
        $course->fill($request->all());
        $course->save();
        return redirect()->route('course.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $instituteIds = Institute::whereUserId(Auth::id())->pluck('id')->toArray();
        $course = Course::findOrFail($id);
        if (!in_array($course->institute_id, $instituteIds) && !Auth::user()->hasRole('admin')) {
            abort(404);
        }
        $course = Course::findOrFail($id);
        $course->delete();
        return redirect()->route('course.index');
    }
}
