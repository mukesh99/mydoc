<?php

namespace App\Http\Controllers;

use App\Coupon;
use App\Course;
use App\Institute;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class CouponController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!empty(Input::get('course_id'))) {
            $coupons = Coupon::whereCourseId(Input::get('course_id'))->paginate(10);
        } else {
            $instituteIds = Institute::whereUserId(Auth::id())->pluck('id')->toArray();
            $courseIds = Course::whereIn('institute_id', $instituteIds)->pluck('id')->toArray();
            $coupons = Coupon::whereIn('course_id', $courseIds)->paginate(10);
        }
        return view('coupon.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $courseId = Input::get('course_id');
        $course = null;
        if($courseId){
            $course = Course::findOrFail($courseId);
        }
        $institutes = Institute::whereUserId(Auth::id())->get();
        return view('coupon.create', compact('institutes','course'));
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
            'course_id' => 'required|exists:courses,id',
            'discount' => 'required|between:1,99',
            'expiry_date' => 'required|after:today|date_format:Y-m-d H:i:s',
            'count' => 'required|between:1,999'
        ]);
        $institute = Institute::findOrFail($request->get('institute_id'));
        $course = Course::findOrFail($request->get('course_id'));
        $lastCoupon = Coupon::whereCourseId($course->id)->orderBy('code','desc')->take(1)->first();
        if(count($lastCoupon)){
            $start = substr($lastCoupon->code,-3) + 1;
            for ($i = $start; $i <= $request->get('count') + $start; $i++) {
                $data = $request->all();
                $data['code'] = strtoupper(str_replace(' ', '', $institute->name . $course->name . str_pad($i, 3, '0', STR_PAD_LEFT)));
                Coupon::create($data);
            }
        }else{
            for ($i = 0; $i < $request->get('count'); $i++) {
                $data = $request->all();
                $data['code'] = strtoupper(str_replace(' ', '', $institute->name . $course->name . str_pad($i, 3, '0', STR_PAD_LEFT)));
                Coupon::create($data);
            }
        }
        return redirect()->route('coupon.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $coupon = Coupon::findOrFail($id);
        if ($coupon->course->institute->user->id != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(404);
        }
        $coupon = Coupon::findOrFail($id);
        return view('coupon.show', compact('coupon'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        if ($coupon->course->institute->user->id != Auth::id() && !Auth::user()->hasRole('admin')) {
            abort(404);
        }
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();
        return redirect()->route('coupon.index');
    }
}
