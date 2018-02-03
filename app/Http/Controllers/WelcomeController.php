<?php

namespace App\Http\Controllers;

use App\Address;
use App\Advertisement;
use App\Institute;
use App\User;
use App\Teacher;
use App\Review;
use App\TopInstitute;
use App\TeacherCourse;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class WelcomeController extends Controller
{
    public function index()
    {
        $topRightAd = Advertisement::wherePosition('top_right')->first();
        $middleScrollAd = Advertisement::wherePosition('middle_area')->get();
        $middleRightAd = Advertisement::wherePosition('middle_right')->first();
        $tops = TopInstitute::with('institute','institute.reviews')->orderBy('position','asc')->take(10)->get();
        return view('welcome', compact('tops','topRightAd','middleScrollAd','middleRightAd'));
    }

    public function getGeoCode($address_id)
    {
        $address = Address::findOrFail($address_id);
        $location = $address->line1 . $address->line2 == null ? '' : $address->line2 . $address->area->name . $address->city->name . $address->state->name . $address->country->name;
        $uri = "https://maps.googleapis.com/maps/api/geocode/json?address=".$location."&key=AIzaSyCezsEIEuXkDS4aBowzEG9Yiphdr3gzrIY";
        $client = new Client();
        $response =  $client->get($uri, []);
        return  json_decode((string)$response->getBody(), true);
    }

    public function getInstitute($institute_id)
    {
        $institute = Institute::findOrFail($institute_id);
        return view('institute',compact('institute'));
    }

     public function getTeacher($teacher_id)
    {
        $teacher = Teacher::with('user')->findOrFail($teacher_id);

        return view('teacher',compact('teacher'));
       
            
    }

    public function search()
    {
        $cityId = Input::get('city');
        $areaId = Input::get('area');
        $discount = Input::get('discount');
        return view('search',compact('cityId','areaId','discount'));

    }

       public function teacher_search()
    {
        $cityId = Input::get('city');
        $areaId = Input::get('area');
        $course = Input::get('course');
        return view('teacher-search',compact('cityId','areaId','course'));
    }

     
}
