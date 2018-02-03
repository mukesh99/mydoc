<?php

namespace App\Http\Controllers;

use App\Area;
use App\Coupon;
use App\Course;
use App\Institute;
use App\Student;
use App\Teacher;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'institutes' => Institute::count(),
            'courses' => Course::count(),
            'areas' => Area::count(),
            'coupons' => Coupon::count(),
            'student' => Student::count(),
            'teacher' => Teacher::count()

        ];
        return view('dashboard',compact('stats'));
    }

    public function location()
    {
        return view('dashboard.location');
    }

    public function user()
    {
        return view('dashboard.user');
    }

    public function institute()
    {
        return view('dashboard.institute');
    }

    public function review()
    {
        return view('dashboard.review');
    }

     public function student()
    {
        return view('dashboard.student');
    }

     public function teacher()
    {
         return view('dashboard.teacher');
    }
}
