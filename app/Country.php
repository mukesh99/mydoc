<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = ['name','is_active'];

    public function states(){
        return $this->hasMany(State::class);
    }
}
