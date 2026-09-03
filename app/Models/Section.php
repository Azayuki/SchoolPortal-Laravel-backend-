<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

// #[Fillable(['name', 'grade_level', 'user_id'])]
class Section extends Model
{

    protected $fillable = ['name', 'grade_level', 'user_id'];

    public function teacher()
    {
        return $this->belongsTo(User::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
