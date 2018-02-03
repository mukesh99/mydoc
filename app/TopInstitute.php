<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TopInstitute extends Model
{
    protected $fillable = [
        'institute_id', 'position'
    ];

    public function institute()
    {
        return $this->belongsTo(Institute::class);
    }
}
