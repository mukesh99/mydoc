<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $fillable = ['city_id','name','is_active'];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
