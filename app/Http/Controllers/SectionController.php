<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;

class SectionController extends Controller
{
    public function index()
    {
        if (!auth()->user()) {
            return $this->Unauthorized("You must be logged in to access this resource.");
        }
        else if (auth()->user()->role !== 'admin') {
            return $this->Forbidden("You do not have permission to access this resource.");
        }
        $sections = Section::all();
        return $this->Success($sections);
    }

    public function show($id)
    {
        if (!auth()->user()) {
            return $this->Unauthorized("You must be logged in to access this resource.");
        }
        else if (auth()->user()->role !== 'admin' && auth()->user()->role !== 'teacher') {
            return $this->Forbidden("You do not have permission to access this resource.");
        }
        $section = Section::find($id);
        if (!$section) {
            return $this->NotFound("Section not found!");
        }
        return $this->Success($section);
    }

    public function store(Request $request)
    {
        if (!auth()->user()) {
            return $this->Unauthorized("You must be logged in to access this resource.");
        }
        else if (auth()->user()->role !== 'admin') {
            return $this->Forbidden("You do not have permission to access this resource.");
        }

        $validator = validator()->make($request->all(), [
            "name" => "required|string|max:255",
            "grade_level" => "required|integer|min:1|max:12",
            "user_id" => "required|exists:users,id"
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }

        $section = Section::create($validator->validated());
        return $this->Created($section);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()) {
            return $this->Unauthorized("You must be logged in to access this resource.");
        }
        else if (auth()->user()->role !== 'admin') {
            return $this->Forbidden("You do not have permission to access this resource.");
        }

        $section = Section::find($id);
        if (!$section) {
            return $this->NotFound("Section not found!");
        }

        $validator = validator()->make($request->all(), [
            "name" => "sometimes|string|max:255",
            "grade_level" => "sometimes|integer|min:1|max:12",
            "user_id" => "sometimes|exists:users,id"
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }

        $section->update($validator->validated());
        return $this->Success($section);
    }

    public function destroy($id)
    {
        if (!auth()->user()) {
            return $this->Unauthorized("You must be logged in to access this resource.");
        }
        else if (auth()->user()->role !== 'admin') {
            return $this->Forbidden("You do not have permission to access this resource.");
        }

        $section = Section::find($id);
        if (!$section) {
            return $this->NotFound("Section not found!");
        }

        $section->delete();
        return $this->Success(null, "Section deleted!");
    }

}
