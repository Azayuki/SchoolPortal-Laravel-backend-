<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Section;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $section = Section::first();

        $user = User::create([
            'username'  => 'student1',
            'email'     => 'student1@school.com',
            'password'  => 'password123',
            'role'      => 'student',
            'is_active' => true,
        ]);

        Student::create([
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
            'section_id' => $section->id,
            'user_id'    => $user->id,
        ]);
    }
}