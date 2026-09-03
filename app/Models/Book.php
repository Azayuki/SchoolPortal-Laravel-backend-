<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

// #[Fillable(['SKU', 'title', 'author', 'year_published'])]
class Book extends Model
{
    protected $fillable = ['SKU', 'title', 'author', 'year_published', 'is_available'];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function borrows()
    {
        return $this->hasMany(Borrow::class);
    }
}
