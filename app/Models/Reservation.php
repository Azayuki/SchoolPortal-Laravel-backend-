<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

// #[Fillable(['reservation_date', 'expiration_date', 'status', 'student_id', 'book_id'])]
class Reservation extends Model
{
    protected $fillable = ['reservation_date', 'expiration_date', 'status', 'student_id', 'book_id'];
    
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
