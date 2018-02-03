<?php

namespace App;

use App\Teacher;
use App\TeacherCourse;
use Illuminate\Database\Eloquent\Model;

class TeacherCoupon extends Model
{
    protected $fillable = [
        'teacher_course_id', 'discount', 'code', 'expiry_date', 'used'
    ];

     public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function teacher_course()
    {
        return $this->belongsTo(TeacherCourse::class);
    }
   

}
