<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Institute extends Model
{
    protected $fillable = [
        'user_id', 'name', 'contact_person', 'email', 'phone', 'about', 'rating', 'address_id', 'facebook', 'twitter', 'g_plus', 'is_active'
    ];

    public function getRouteKeyName()
    {
        return 'name';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function institutePics()
    {
        return $this->hasMany(InstitutePic::class);
    }

    public function placements()
    {
        return $this->hasMany(Placement::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class)->with('country')->with('state')->with('city')->with('area');
    }

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}
