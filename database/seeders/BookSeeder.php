<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        Book::create([
            'SKU'            => 'BK-001',
            'title'          => 'Clean Code',
            'author'         => 'Robert C. Martin',
            'year_published' => 2008,
            'is_available'   => true,
        ]);

        Book::create([
            'SKU'            => 'BK-002',
            'title'          => 'The Pragmatic Programmer',
            'author'         => 'Andy Hunt',
            'year_published' => 1999,
            'is_available'   => true,
        ]);

        Book::create([
            'SKU'            => 'BK-003',
            'title'          => 'Design Patterns',
            'author'         => 'Erich Gamma',
            'year_published' => 1994,
            'is_available'   => false,
        ]);
    }
}