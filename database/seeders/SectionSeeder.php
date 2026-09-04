<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\User;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('role', 'teacher')
                       ->where('is_active', true)
                       ->first();

        Section::create([
            'name'        => 'Grade 10 - Einstein',
            'grade_level' => 10,
            'user_id'     => $teacher->id,
        ]);

        Section::create([
            'name'        => 'Grade 11 - Curie',
            'grade_level' => 11,
            'user_id'     => $teacher->id,
        ]);
    }
}