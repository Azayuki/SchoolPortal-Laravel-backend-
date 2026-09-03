<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;

class StudentController extends Controller
{
    public function index()
    {
        if(!auth()->user() || auth()->user()->role !== 'admin'){
            return $this->Unauthorized("You are not authorized to view students.");
        }
        $students = Student::with('user')->get();
        return $this->Success($students);
    }

    public function show($id)
    {
        if(!auth()->user() || auth()->user()->role !== 'admin'){
            return $this->Unauthorized("You are not authorized to view this student.");
        }
        $student = Student::with('user')->find($id);
        if (!$student) {
            return $this->NotFound("Student not found!");
        }
        return $this->Success($student);
    }
}
