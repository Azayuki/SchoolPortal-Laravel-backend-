<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

// #[Fillable(['borrow_date', 'due_date', 'return_date', 'student_id', 'book_id', 'status'])]
class Borrow extends Model
{
    protected $fillable = ['borrow_date', 'due_date', 'return_date', 'student_id', 'book_id', 'status'];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
