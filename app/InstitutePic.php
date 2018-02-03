<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class InstitutePic extends Model
{
    protected $fillable = [
        'institute_id', 'image'
    ];

    public function getImageAttribute($value)
    {
        return asset('storage/'.$value);
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }
}
