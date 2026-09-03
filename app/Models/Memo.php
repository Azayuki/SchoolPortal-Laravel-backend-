<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

// #[Fillable(['title', 'content', 'category'])]
class Memo extends Model
{
    protected $fillable = ['title', 'content', 'category'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('is_read');
    }
}
