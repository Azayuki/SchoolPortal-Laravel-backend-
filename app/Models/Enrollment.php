<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

// #[Fillable(['start_year', 'end_year', 'status', 'student_id'])]
class Enrollment extends Model
{
    protected $fillable = ['start_year', 'end_year', 'status', 'student_id'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
