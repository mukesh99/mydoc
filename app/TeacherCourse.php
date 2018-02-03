<?php

namespace App;

use App\Teacher;
use App\TeacherCoupon;
use App\User;
use Illuminate\Database\Eloquent\Model;

class TeacherCourse extends Model
{
      protected $fillable = [
        'teacher_id', 'name', 'description', 'exp_time', 'exp_course', 'price'
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function teacher_coupons()
    {
        return $this->hasMany(TeacherCoupon::class);
    }
}
