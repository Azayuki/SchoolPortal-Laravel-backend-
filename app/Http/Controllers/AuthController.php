<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function registerAsStudent(Request $request){
        $validator = validator()->make($request->all(), [
            "username" => "required|alpha_dash|min:4|max:64|unique:users",
            "email" => "required|email|unique:users",
            "password" => "required|min:8",
            "first_name" => "required|alpha|min:2|max:64",
            "last_name" => "required|alpha|min:2|max:64",
            "section_id" => "required|exists:sections,id",
        ]);

        if($validator->fails()){
            return $this->BadRequest($validator);
        }

        $user = User::create($validator->validated());
        $user->student()->create($validator->validated());

        return $this->Created($user);
    }
    
    public function registerAsEmployee(Request $request){
        $validator = validator()->make($request->all(), [
            "username" => "required|alpha_dash|min:4|max:64|unique:users",
            "email" => "required|email|unique:users",
            "password" => "required|min:8",
            "role" => "required|in:admin,teacher,principal",
        ]);

        if($validator->fails()){
            return $this->BadRequest($validator);
        }

        $data = $validator->validated();

        $data['is_active'] = false; // Set is_active to false for employees

        $user = User::create($data);

        return $this->Created($user);
    }

    public function login(Request $request){

        $validator = validator()->make($request->all(), [
            "email" => "required",
            "password" => "required",
        ]);

        if($validator->fails()){
            return $this->BadRequest($validator);
        }

        if(!auth()->attempt($validator->validated())){
            return $this->Unauthorized("Invalid credentials!");
        };

        $user = auth()->user();

        if(!$user->is_active){
            return $this->Unauthorized("Your account is not active. Please contact the administrator.");
        }

        $user -> token = $user->createToken("api")->plainTextToken;

        return $this->Success($user, "Logged in!");
    }
    public function logout(Request $request){
    
        $request->user()->currentAccessToken()->delete();
        
        return $this->Success(null, "Logged out!");
}

    public function checkAuth(Request $request){
        $user = request()->user();
        if(!$user){
            return $this->Unauthorized();
        }   

        return $this->Success($user, "Authenticated!");
    }
}
