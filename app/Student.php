<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
     protected $fillable = ['user_id', 'profile_pic', 'line2', 'course_name', 'learn_place' , 'learn_time' , 'qualification' , 'job_type', 'level' ,'exp_years','exp_months','exp_days','job_location' ,'resume'
     ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
