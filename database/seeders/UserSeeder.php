<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin (active)
        User::create([
            'username'  => 'admin',
            'email'     => 'admin@school.com',
            'password'  => 'password123',
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // Principal (active)
        User::create([
            'username'  => 'principal',
            'email'     => 'principal@school.com',
            'password'  => 'password123',
            'role'      => 'principal',
            'is_active' => true,
        ]);

        // Teacher (active)
        User::create([
            'username'  => 'teacher',
            'email'     => 'teacher@school.com',
            'password'  => 'password123',
            'role'      => 'teacher',
            'is_active' => true,
        ]);

        // Inactive teacher (for testing activation)
        User::create([
            'username'  => 'inactive_teacher',
            'email'     => 'inactive@school.com',
            'password'  => 'password123',
            'role'      => 'teacher',
            'is_active' => false,
        ]);
    }
}