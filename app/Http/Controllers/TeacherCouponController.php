<?php

namespace App\Http\Controllers;

use App\Teacher;
use App\TeacherCoupon;
use App\TeacherCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class TeacherCouponController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      if (!empty(Input::get('teacher_course_id'))) {
            $teacher_coupons = TeacherCoupon::whereTeacherCourseId(Input::get('teacher_course_id'))->paginate(10);
        } else {
            $teacherIds = Teacher::whereUserId(Auth::id())->pluck('id')->toArray();
            $teachercourseIds = TeacherCourse::whereIn('teacher_id', $teacherIds)->pluck('id')->toArray();
            $teacher_coupons = TeacherCoupon::whereIn('teacher_course_id', $teachercourseIds)->paginate(10);
        }
        return view('teacher_coupon.index', compact('teacher_coupons'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
       
        $teachercourseId = Input::get('teacher_course_id');
        $teacher_course = null;
        if($teachercourseId){
            $teacher_course = TeacherCourse::findOrFail($teachercourseId);
        }
        $teachers = Teacher::whereUserId(Auth::id())->get();
        return view('teacher_coupon.create', compact('teachers','teacher_course'));
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
            'teacher_id' => 'required|exists:teachers,id|in:' . implode(',', Teacher::whereUserId(Auth::user()->id)->pluck('id')->toArray()),
            'teacher_course_id' => 'required|exists:teacher_courses,id',
            'discount' => 'required|between:1,99',
            'expiry_date' => 'required|after:today|date_format:Y-m-d H:i:s',
            'count' => 'required|between:1,999'
        ]);
        $teacher = Teacher::findOrFail($request->get('teacher_id'));
        $teacher_course = TeacherCourse::findOrFail($request->get('teacher_course_id'));
        $lastCoupon = TeacherCoupon::whereTeacherCourseId($teacher_course->id)->orderBy('code','desc')->take(1)->first();
        if(count($lastCoupon)){
            $start = substr($lastCoupon->code,-3) + 1;
            for ($i = $start; $i <= $request->get('count') + $start; $i++) {
                $data = $request->all();
                $data['code'] = strtoupper(str_replace(' ', '', $teacher->user->name . $teacher_course->name . str_pad($i, 3, '0', STR_PAD_LEFT)));
                TeacherCoupon::create($data);
            }
        }else{
            for ($i = 0; $i < $request->get('count'); $i++) {
                $data = $request->all();
                $data['code'] = strtoupper(str_replace(' ', '', $teacher->user->name . $teacher_course->name . str_pad($i, 3, '0', STR_PAD_LEFT)));
                TeacherCoupon::create($data);
            }
        }
        return redirect()->route('teacher_coupon.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $teacher_coupon = TeacherCoupon::findOrFail($id);
        if ($teacher_coupon->teacher_course->teacher->user->id != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(404);
        }
        $teacher_coupon = TeacherCoupon::findOrFail($id);
        return view('teacher_coupon.show', compact('teacher_coupon'));
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $teacher_coupon = TeacherCoupon::findOrFail($id);
        if ($teacher_coupon->teacher_course->teacher->user->id != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(404);
        }
        $teacher_coupon = TeacherCoupon::findOrFail($id);
        $teacher_coupon->delete();
        return redirect()->route('teacher_coupon.index');
    }
}
