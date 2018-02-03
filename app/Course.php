<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'institute_id', 'name', 'description', 'start_date', 'end_date', 'price'
    ];

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }

    public function coupons()
    {
        return $this->hasMany(Coupon::class);
    }
}
