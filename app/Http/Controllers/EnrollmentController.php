<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Enrollment;

class EnrollmentController extends Controller
{
    public function index()
    {
        if(!auth()->user() || auth()->user()->role !== 'admin'){
            return $this->Unauthorized("You are not authorized to view enrollments.");
        }
        $enrollments = Enrollment::with('student')->get();
        return $this->Success($enrollments);
    }

    public function show($id)
    {
        if(!auth()->user() || auth()->user()->role !== 'admin'){
            return $this->Unauthorized("You are not authorized to view this enrollment.");
        }
        $enrollment = Enrollment::with('student')->find($id);
        if (!$enrollment) {
            return $this->NotFound("Enrollment not found!");
        }
        return $this->Success($enrollment);
    }

    public function store(Request $request)
    {
        if(!auth()->user() || auth()->user()->role !== 'admin'){
            return $this->Unauthorized("You are not authorized to create an enrollment.");
        }

        $validator = validator()->make($request->all(), [
            "student_id" => "required|exists:students,id",
            "start_year"=> "required|integer|min:1900|max:".(date("Y")+1),
            "end_year"=> "required|integer|min:1900|max:".(date("Y")+1)."|gte:start_year",
            "status" => "required|string|in:enrolled,withdrawn,graduated",
        ]);

        if($validator->fails()){
            return $this->BadRequest($validator);
        }

        $enrollment = Enrollment::create($validator->validated());
        return $this->Created($enrollment);
    }

    public function update(Request $request, $id)
    {
        if(!auth()->user() || (auth()->user()->role !== 'admin')){
            return $this->Unauthorized("You are not authorized to update this enrollment.");
        }

        $enrollment = Enrollment::find($id);
        if (!$enrollment) {
            return $this->NotFound("Enrollment not found!");
        }

        $validator = validator()->make($request->all(), [
            "student_id" => "sometimes|required|exists:students,id",
            "start_year"=> "sometimes|required|integer|min:1900|max:".(date("Y")+1),
            "end_year"=> "sometimes|required|integer|min:1900|max:".(date("Y")+1)."|gte:start_year",
            "status" => "sometimes|required|string|in:enrolled,withdrawn,graduated",
        ]);

        if($validator->fails()){
            return $this->BadRequest($validator);
        }

        $enrollment->update($validator->validated());
        return $this->Success($enrollment, "Enrollment updated successfully.");
    }

    public function destroy($id)
    {
        if(!auth()->user() || auth()->user()->role !== 'admin'){
            return $this->Unauthorized("You are not authorized to delete this enrollment.");
        }

        $enrollment = Enrollment::find($id);
        if (!$enrollment) {
            return $this->NotFound("Enrollment not found!");
        }

        $enrollment->delete();
        return $this->Success($enrollment, "Enrollment deleted successfully.");
    }
}
