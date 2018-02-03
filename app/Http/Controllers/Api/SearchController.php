<?php

namespace App\Http\Controllers\Api;

use App\Address;
use App\Area;
use App\City;
use App\Coupon;
use App\Course;
use App\Institute;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Mockery\Exception;

class SearchController extends Controller
{
    use ApiResponse;

    public function index()
    {
        try {
            //init variables
            $city_id = Input::get('city_id');
            $areas = Input::get('areas');
            $discounts = Input::get('discount');
            $courses = Input::get('course');
            $sort = Input::get('sort');
            $prices = Input::get('price');
            //end init
            $institutes = Institute:: whereHas('address', function ($query) use ($city_id, $areas) {
                $query->whereCityId($city_id);
                if (isset($areas)) {
                    $query->whereIn('area_id', $areas);
                }
            })->whereHas('courses', function ($query) use ($courses, $discounts, $prices) {
                if (isset($courses)) {
                    $query->whereIn('name', $courses);
                }
                if (isset($prices)) {
                    $query->whereIn('price', $prices);
                }
                if (isset($discounts)) {
                    $query->whereHas('coupons', function ($query) use ($discounts) {
                        $query->whereIn('discount', $discounts);
                    });
                }
            })->when($sort, function ($query) use ($sort) {
                if ($sort === 'discount_asc') {
                    $query->with(['courses.coupons' => function ($query) {
                        $query->orderBy('discount', 'asc');
                    }]);
                }
                if ($sort === 'discount_desc') {
                    $query->with(['courses.coupons' => function ($query) {
                        $query->orderBy('discount', 'desc');
                    }]);
                }
                if ($sort === 'institute_name_asc') {
                    $query->orderBy('name', 'asc');
                }
                if ($sort === 'institute_name_desc') {
                    $query->orderBy('name', 'desc');
                }
                if ($sort === 'course_created_at_asc') {
                    $query->with(['courses' => function ($query) {
                        $query->orderBy('created_at', 'asc');
                    }]);
                }
            })->with('address', 'institutePics', 'courses', 'courses.coupons', 'placements', 'reviews')->whereIsActive(true)->paginate(10);
            return response()->json($this->onSuccess($institutes, 'all institutes'));
        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }

    public function location()
    {
        try {
            $city_id = Input::get('city_id');
            $name = Input::get('location');
            $areas = new Collection();
            Institute::whereHas('address', function ($query) use ($city_id,$name) {
                $query->whereCityId($city_id)->whereHas('area',function ($query) use ($name){
                    $query->where('name','like','%'.$name.'%');
                });
            })->with('address.area')->get()->filter(function ($institute) use ($areas){
                $areas->push($institute->address->area);
            });
            return response()->json($this->onSuccess($areas->unique('name'), 'search results for locations'));
        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }

    public function discount()
    {
        try {
            $city_id = Input::get('city_id');
            $areas = Input::get('areas');
            $coupons = Coupon::whereHas('course', function ($query) use ($city_id, $areas) {
                $query->whereHas('institute', function ($query) use ($city_id, $areas) {
                    $query->whereHas('address', function ($query) use ($city_id, $areas) {
                        $query->whereCityId($city_id);
                        if (isset($areas)) {
                            $query->whereIn('area_id', $areas);
                        }
                    })->whereIsActive(true);
                });
            })->distinct()->orderBy('discount')->get(['discount']);
            return response()->json($this->onSuccess($coupons, 'all coupons'));
        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }

    public function course()
    {
        try {
            $city_id = Input::get('city_id');
            $areas = Input::get('areas');
            $courses = Course::whereHas('institute', function ($query) use ($city_id, $areas) {
                $query->whereHas('address', function ($query) use ($city_id, $areas) {
                    $query->whereCityId($city_id);
                    if (isset($areas)) {
                        $query->whereIn('area_id', $areas);
                    }
                })->whereIsActive(true);
            })->distinct()->orderBy('name')->get(['name']);
            return response()->json($this->onSuccess($courses, 'all courses'));
        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }

    public function price()
    {
        try {
            $city_id = Input::get('city_id');
            $areas = Input::get('areas');
            $prices = Course::whereHas('institute', function ($query) use ($city_id, $areas) {
                $query->whereHas('address', function ($query) use ($city_id, $areas) {
                    $query->whereCityId($city_id);
                    if (isset($areas)) {
                        $query->whereIn('area_id', $areas);
                    }
                })->whereIsActive(true);
            })->distinct('price')->orderBy('price')->get(['price']);
            return response()->json($this->onSuccess($prices, 'min and max'));
        } catch (Exception $exception) {
            return response()->json($exception->getMessage());
        }
    }
}
