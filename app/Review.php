<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'institute_id', 'rating', 'description'
    ];

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }
}
