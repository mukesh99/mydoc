<?php

//use Illuminate\Http\Request;

Route::prefix('search')->namespace('Api')->group(function () {
    Route::get('/', 'SearchController@index');
    Route::get('/location','SearchController@location');
    Route::get('/discount','SearchController@discount');
    Route::get('/course','SearchController@course');
    Route::get('/price','SearchController@price');
});

Route::prefix('auth')->namespace('Api')->group(function () {
    Route::post('register', 'AuthController@registerUser');
    Route::post('login', 'AuthController@loginUser');
});

Route::namespace('Api')->middleware('api')->group(function () {
    Route::resource('country', 'CountryController');
    Route::resource('state', 'StateController');
    Route::resource('city', 'CityController');
    Route::resource('area', 'AreaController');
    Route::resource('user','UserController');
    Route::resource('institute','InstituteController');
    Route::resource('coupon','CouponController');
   
});

Route::namespace('Api')->prefix('help')->group(function (){
    Route::get('user/institute','HomeController@userInstitutes');
    Route::get('user/course/{institute_id}','HomeController@instituteCourses');
    Route::get('discount/{area_id}','HomeController@getDiscount');
    Route::post('send/otp','HomeController@sendOtp');
    Route::post('verify/otp','HomeController@verifyOtp');
    Route::resource('review','ReviewController');
    Route::get('area','HomeController@getAreasHasDiscount');
    Route::get('rating/{institute_id}','HomeController@getRating');
    Route::get('rating/dist/{institute_id}','HomeController@getRatingDistribution');
});
