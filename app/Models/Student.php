<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;


// #[Fillable(['first_name', 'last_name', 'section_id', 'user_id'])]
class Student extends Model
{

    protected $fillable = ['first_name', 'last_name', 'section_id', 'user_id'];
    #Foreign Key Relationships
    public function section()
    {
        return $this->belongsTo(Section::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }
    
    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}
