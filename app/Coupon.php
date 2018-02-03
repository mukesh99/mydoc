<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'course_id', 'discount', 'code', 'expiry_date', 'used'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
