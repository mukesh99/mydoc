<?php

use App\Institute;
use App\Role;
use App\User;
use Illuminate\Support\Facades\Artisan;

Auth::routes();

Route::get('/', 'WelcomeController@index');
Route::get('/geocode/{address_id}', 'WelcomeController@getGeoCode');
Route::get('/institute/show/{id}', 'WelcomeController@getInstitute');
Route::get('/teacher/show/{id}','WelcomeController@getTeacher');

Route::get('search', 'WelcomeController@search');
Route::get('teacher-search','WelcomeController@teacher_search');

Route::middleware('auth')->group(function () {

    // authenticated users

    Route::get('/home', 'HomeController@index')->name('home');


    Route::middleware('role:student')->group(function () {
        Route::resource('student', 'StudentController');


    });

    Route::middleware('role:teacher')->group(function () {
        Route::resource('teacher', 'TeacherController');
        Route::resource('teacher_course', 'TeacherCourseController');
        Route::resource('teacher_coupon', 'TeacherCouponController');
    });

    Route::middleware('role:admin|owner')->group(function () {
        Route::resource('profile', 'ProfileController');
        Route::resource('institute', 'InstituteController');
        Route::resource('course', 'CourseController');
        Route::resource('coupon', 'CouponController');
        Route::resource('placement', 'PlacementController');
    });

    // admin user
    Route::middleware('role:admin')->prefix('dashboard')->group(function () {
        Route::get('/', 'DashboardController@dashboard')->name('dashboard');
        Route::get('location', 'DashboardController@location')->name('dashboard.location');
        Route::get('user', 'DashboardController@user')->name('dashboard.user');
        Route::get('institute', 'DashboardController@institute')->name('dashboard.institute');
        Route::resource('top', 'TopInstituteController');
        Route::resource('advertise', 'AdvertiseController');
        Route::get('review', 'DashboardController@review')->name('dashboard.review');
    });
});


Route::get('/test', function () {
//    $admin = new Role();
//    $admin->name = 'owner';
//    $admin->display_name = 'User is a owner of an institute'; // optional
//    $admin->description = 'User is a owner of an institute'; // optional
//    $admin->save();
//
//    $admin = new Role();
//    $admin->name = 'student';
//    $admin->display_name = 'User is a student'; // optional
//    $admin->description = 'User is a student'; // optional
//    $admin->save();
//
//    $admin = new Role();
//    $admin->name = 'teacher';
//    $admin->display_name = 'User is a teacher'; // optional
//    $admin->description = 'User is a teacher'; // optional
//    $admin->save();
//
//    $admin = new Role();
//    $admin->name = 'admin';
//    $admin->display_name = 'User is a admin of site'; // optional
//    $admin->description = 'User is can manage users'; // optional
//    $admin->save();

    $role = Role::whereName('admin')->first();
    $user = User::whereEmail('m@gmail.com')->first();
    $user->attachRole($role);

});

Route::get('/link', function () {
    $exitCode = Artisan::call('migrate');
});

Route::get('/lin', function () {
    symlink('/home/succepih/public_html/discountjee.com/framework/storage/app/public', '/home/succepih/public_html/discountjee.com/storage');
});

Route::get('/exp', function () {
    $city_id = 1;
    $name = 'bas';
    $areas = new \Illuminate\Support\Collection();
    Institute::whereHas('address', function ($query) use ($city_id, $name) {
        $query->whereCityId($city_id)->whereHas('area', function ($query) use ($name) {
            $query->where('name', 'like', '%' . $name . '%');
        });
    })->with('address.area')->get()->filter(function ($institute) use ($areas) {
        $areas->push($institute->address->area);
    });
    return $areas;
});