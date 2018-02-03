<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Placement extends Model
{
    protected $fillable = [
        'institute_id', 'student_pic'
    ];

    public function getStudentPicAttribute($value)
    {
        return asset('storage/'.$value);
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }
}
