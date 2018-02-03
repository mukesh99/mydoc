<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CouponUser extends Model
{
    protected $fillable = [
        'phone', 'otp', 'attempt', 'course_id'
    ];
}
