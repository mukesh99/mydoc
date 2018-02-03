<?php

namespace App\Http\Controllers\Api;

use App\Address;
use App\Area;
use App\Coupon;
use App\CouponUser;
use App\Course;
use App\Http\Controllers\Controller;
use App\Institute;
use App\Review;
use App\Teacher;
use App\TeacherCoupon;
use App\TeacherCouponUser;
use App\TeacherCourse;
use App\Traits\ApiResponse;
use App\Traits\SmsClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

class HomeController extends Controller
{
    use ApiResponse, SmsClient;

    public function userInstitutes()
    {
        try {
            $institutes = Institute::whereUserId(Auth::id())->get();
            return response()->json($this->onSuccess($institutes, 'all institutes'));
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }

    public function instituteCourses($instituteId)
    {
        try {
            $courses = Course::whereInstituteId($instituteId)->get();
            return response()->json($this->onSuccess($courses, 'all courses'));
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }

    public function teacherCourses($teacherId)
    {
        try {
            $teacher_courses = TeacherCourse::whereTeacherId($teacherId)->get();
            return response()->json($this->onSuccess($teacher_courses, 'all courses'));
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }



    public function getAreasHasDiscount(){
        try{
            $city_id = Input::get('city_id');
            $areas = Area::join('addresses','areas.id','=','addresses.area_id')
                            ->join('institutes','addresses.id','=','institutes.address_id')
                            ->join('courses','courses.institute_id','=','institutes.id')
                            ->join('coupons','coupons.course_id','=','courses.id')->distinct('areas.name')->whereNotNull('coupons.discount')->get(['areas.*']);
            return response()->json($this->onSuccess($areas,'all areas'));
        }catch(Exception $exception){
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }

    public function getAreasHasTeacherCourse(){
        try{
            $city_id = Input::get('city_id');
            $areas = Area::join('addresses','areas.id','=','addresses.area_id')
                            ->join('teachers','addresses.id','=','teachers.address_id')
                            ->join('teacher_courses','teacher_courses.teacher_id','=','teachers.id')->distinct('areas.name')->whereNotNull('teacher_courses.name')->get(['areas.*']);
            return response()->json($this->onSuccess($areas,'all areas'));
        }catch(Exception $exception){
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }

  
    

    public function getDiscount($area_id)
    {
        try {
            $addressIds = Address::whereAreaId($area_id)->pluck('id')->toArray();
            $instituteIds = Institute::whereIn('address_id', $addressIds)->pluck('id')->toArray();
            $courseIds = Course::whereIn('institute_id', $instituteIds)->pluck('id')->toArray();
            $discounts = Coupon::whereIn('course_id', $courseIds)->distinct('discount')->pluck('discount')->toArray();
            return response()->json($this->onSuccess($discounts, 'add discounts'));
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }

     public function getTeacherCourse($area_id)
    {
        try {
            $addressIds = Address::whereAreaId($area_id)->pluck('id')->toArray();
            $teacherIds = Teacher::whereIn('address_id', $addressIds)->pluck('id')->toArray();
            $teachercourseIds = TeacherCourse::whereIn('teacher_id', $teacherIds)->distinct('name')->pluck('name')->toArray();
            return response()->json($this->onSuccess($teachercourseIds, 'add discounts'));
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }

   


    public function sendOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:courses,id',
                'phone' => 'required|digits:10',
            ]);
            if ($validator->fails()) {
                return response()->json($this->onFailure($validator->errors(), 'validation errors'));
            } else {
                $coupons = Coupon::whereCourseId($request->get('course_id'))->whereUsed(false)->get();
                if(count($coupons) < 1){
                    return response()->json($this->onFailure(null, "Something went wrong"));
                }
                $requestedOrNot = CouponUser::wherePhone($request->get('phone'))->whereCourseId($request->get('course_id'))->first();
                if ($requestedOrNot) {
                    return response()->json($this->onFailure($request->get('phone'), 'Otp has been already sent'));
                }
                $data = ['phone' => $request->get('phone'), 'otp' => mt_rand(100000, 999999), 'course_id' => $request->get('course_id')];
                CouponUser::create($data);
                $this->sendSms($request->get('phone'), 'Your otp is ' . $data['otp']);
                return response()->json($this->onSuccess($request->get('phone'), 'otp sent'));
            }
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }

    public function verifyOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:courses,id',
                'phone' => 'required|digits:10',
                'otp' => 'required|digits:6'
            ]);
            if ($validator->fails()) {
                return response()->json($this->onFailure($validator->errors(), 'validation errors'));
            } else {
                $couponUser = CouponUser::wherePhone($request->get('phone'))->whereCourseId($request->get('course_id'))->first();
                if (!$couponUser) {
                    return response()->json($this->onFailure(null, "Something went wrong"));
                }
                if ($couponUser->attempt > 3) {
                    $otp = mt_rand(100000, 999999);
                    $couponUser->otp = $otp;
                    $couponUser->attempt = 0;
                    $couponUser->save();
                    $this->sendSms($request->get('phone'), 'Your otp is ' . $otp);
                    return response($this->onFailure(null,'Attempts exceeded. We have resent an otp'));
                } else if ($couponUser->attempt == -1) {
                    return response($this->onFailure(null,'You have used this coupon code already.'));
                } else if ($request->get('otp') == $couponUser->otp) {
                    $coupon = Coupon::whereCourseId($request->get('course_id'))->whereUsed(false)->first();
                    if(!$coupon){
                        return response()->json($this->onFailure(null, "Something went wrong"));
                    }
                    $this->sendSms($request->get('phone'),'Your coupon code is '.$coupon->code);
                    $couponUser->attempt = -1;
                    $couponUser->save();
                    $coupon->used = true;
                    $coupon->save();
                    $this->sendSms($coupon->course->institute->phone,'Hello Admin, User mobile '.$request->get('phone').' is applied for '.$coupon->code);
                    return response($this->onSuccess(null,'We have sent coupon code to your phone number.'));
                }else{
                    $couponUser->attempt += 1;
                    $couponUser->save();
                    return response($this->onFailure(null,'Otp entered is invalid.'));
                }
            }
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }

     public function sendTeacherOtp(Request $request)
    {
       try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:teacher_courses,id',
                'phone' => 'required|digits:10',
            ]);
            if ($validator->fails()) {
                return response()->json($this->onFailure($validator->errors(), 'validation errors'));
            } else {

                $teacher_coupons = TeacherCoupon::whereTeacherCourseId($request->get('course_id'))->whereUsed(false)->get();
                if(count($teacher_coupons) < 1){
                    return response()->json($this->onFailure(null, "Something went wrong"));
                }
                $requestedOrNot = TeacherCouponUser::wherePhone($request->get('phone'))->whereCourseId($request->get('course_id'))->first();
                if ($requestedOrNot) {
                    return response()->json($this->onFailure($request->get('phone'), 'Otp has been already sent'));
                }
                $data = ['phone' => $request->get('phone'), 'otp' => mt_rand(100000, 999999), 'course_id' => $request->get('course_id')];
                TeacherCouponUser::create($data);
                $this->sendSms($request->get('phone'), 'Your otp is ' . $data['otp']);
                return response()->json($this->onSuccess($request->get('phone'), 'otp sent'));
            }
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }

    public function verifyTeacherOtp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:teacher_courses,id',
                'phone' => 'required|digits:10',
                'otp' => 'required|digits:6'
            ]);
            if ($validator->fails()) {
                return response()->json($this->onFailure($validator->errors(), 'validation errors'));
            } else {
                $couponUser = TeacherCouponUser::wherePhone($request->get('phone'))->whereCourseId($request->get('course_id'))->first();
                if (!$couponUser) {
                    return response()->json($this->onFailure(null, "Something went wrong"));
                }
                if ($couponUser->attempt > 3) {
                    $otp = mt_rand(100000, 999999);
                    $couponUser->otp = $otp;
                    $couponUser->attempt = 0;
                    $couponUser->save();
                    $this->sendSms($request->get('phone'), 'Your otp is ' . $otp);
                    return response($this->onFailure(null,'Attempts exceeded. We have resent an otp'));
                } else if ($couponUser->attempt == -1) {
                    return response($this->onFailure(null,'You have used this coupon code already.'));
                } else if ($request->get('otp') == $couponUser->otp) {
                    $coupon = TeacherCoupon::whereTeacherCourseId($request->get('course_id'))->whereUsed(false)->first();
                    if(!$coupon){
                        return response()->json($this->onFailure(null, "Something went wrong"));
                    }
                    $this->sendSms($request->get('phone'),'Your coupon code is '.$coupon->code);
                    $couponUser->attempt = -1;
                    $couponUser->save();
                    $coupon->used = true;
                    $coupon->save();
                    /*$this->sendSms($coupon->course->teacher->user->phone,'Hello Admin, User mobile '.$request->get('phone').' is applied for '.$coupon->code);*/
                    return response($this->onSuccess(null,'We have sent coupon code to your phone number.'));
                }else{
                    $couponUser->attempt += 1;
                    $couponUser->save();
                    return response($this->onFailure(null,'Otp entered is invalid.'));
                }
            }
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }


    public function getRating($institute_id)
    {
        try {
            $rating = Review::whereInstituteId($institute_id)->select(DB::raw('round(avg(rating),1) as rating'),DB::raw('count(rating) as count'))->get();
            return response()->json($this->onSuccess($rating, 'average rating'));
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }

    public function getRatingDistribution($institute_id)
    {
        try {
            $rating = Review::whereInstituteId($institute_id)->select(DB::raw('rating, count(rating) as count'))->groupBy('rating')->orderBy('rating','desc')->get();
            return response()->json($this->onSuccess($rating, 'average rating'));
        } catch (Exception $exception) {
            return response()->json($this->onFailure(null, $exception->getMessage()));
        }
    }
}
