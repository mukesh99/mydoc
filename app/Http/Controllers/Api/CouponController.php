<?php

namespace App\Http\Controllers\Api;

use App\Coupon;
use App\Course;
use App\Http\Controllers\Controller;
use App\Institute;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Mockery\Exception;

class CouponController extends Controller
{
    use ApiResponse;

    public function __construct()
    {
        $this->middleware(['auth:api']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            if (!empty(Input::get('course_id'))) {
                $coupons = Coupon::whereCourseId(Input::get('course_id'))->paginate(10);
            } else {
                $instituteIds = Institute::whereUserId(Auth::id())->pluck('id')->toArray();
                $courseIds = Course::whereIn('institute_id', $instituteIds)->pluck('id')->toArray();
                $coupons = Coupon::with('course','course.institute')->whereIn('course_id', $courseIds)->paginate(10);
            }
            return response()->json($this->onSuccess($coupons, "all coupons"));
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        try {
            $coupons = Coupon::destroy(explode(',',$id));
            return response()->json($this->onSuccess($coupons, "all coupons deleted"));
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }
}
