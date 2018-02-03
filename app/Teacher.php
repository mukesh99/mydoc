<?php

namespace App;

use App\Address;
use App\TeacherCoupon;
use App\TeacherCourse;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Teacher extends Model
{
   protected $fillable = ['user_id', 'profile_pic', 'address_id', 'total_exp', 'total_rel_exp' , 'prefer_time' , 'teach_place' , 'charges', 'sample_details' ,'sample_link','linkedin_id','resume'
     ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function teacher_courses()
    {
        return $this->hasMany(TeacherCourse::class);
    }

   
    
    public function address()
    {
        return $this->belongsTo(Address::class)->with('country')->with('state')->with('city')->with('area');
    }

    public function getProfilePicAttribute($value){
        return Storage::url($value);
    }
}
