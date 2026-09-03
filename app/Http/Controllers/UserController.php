<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
    * Display a listing of the resource.
    */
  public function index(){

    if(!auth()->user() || auth()->user()->role !== 'admin'){
        return $this->Unauthorized("You are not authorized to view users.");
    }

    $users = User::all();

    return $this->Success($users);
}

    /**
     * Store a newly created resource in storage.
     */
  public function store(Request $request){

    if(!auth()->user() || auth()->user()->role !== 'admin'){
        return $this->Unauthorized("You are not authorized to create a user.");
    }

    $inputs = $request->all();

    $validator = validator()->make($inputs, [
        "username" => "required|alpha_dash|unique:users|min:4|max:32",
        "email" => "required|email|unique:users,email|min:8|max:64",
        "password" => "required|string|min:8",
        "role" => "required|in:admin,teacher,principal"
    ]);

    if($validator->fails()){
        return $this->BadRequest($validator);
    }
    $data = $validator->validated();


    $user = User::create($data);

    return $this->Created($user);
}
    
        /**
         * Display the specified resource.
         */
   
   
    public function show(string $id){

        if(!auth()->user() || auth()->user()->role !== 'admin'){
            return $this->Unauthorized("You are not authorized to view this user.");
        }

        $users = User::find($id);
        
        if(empty($users)){
            return $this->NotFound();
        }
        return $this->Success($users);
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(string $id){
        $user = User::find($id);
        if(!$user){
            return $this->NotFound();
        }

        if(!auth()->user() || auth()->user()->role !== 'admin'){
            return $this->Unauthorized("You are not authorized to update this user.");
        }

        $validator = validator()->make(request()->all(), [
            "username" => "sometimes|alpha_dash|unique:users,username,$id|min:4|max:32",
            "email" => "sometimes|email|unique:users,email,$id|min:8|max:64",
            "password" => "sometimes|string|min:8",
            "role" => "sometimes|in:admin,teacher,principal",
            "is_active" => "sometimes|boolean"
        ]);

        if($validator->fails()){
            return $this->BadRequest($validator);
        }

        $data = $validator->validated();

        $user->update($data);

        return $this->Success($user, "Updated");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id){

        if(!auth()->user() || auth()->user()->role !== 'admin'){
            return $this->Unauthorized("You are not authorized to delete this user.");
        }

        $user = User::find($id);
        if(!$user){
            return $this->NotFound();
        }
        
        if ($user->student) {
            $user->student()->delete();
        }
        $user->delete();
        return $this->Success($user, "Deleted");
    }

    public function me(){
    
        $user = auth()->user();
        if (!$user) {
            return $this->NotFound("User not found.");
        }
        if ($user->role === 'student') {
            $user->load('student');
        }
        return $this->Success($user);
    }

    public function showInactiveUsers(){
        if(!auth()->user() || auth()->user()->role !== 'admin'){
            return $this->Unauthorized("You are not authorized to view inactive users.");
        }
    
        $inactiveUsers = User::where('is_active', false)->get();
        return $this->Success($inactiveUsers);
    }

    public function bulkActivateUsers(){
        if(!auth()->user() || auth()->user()->role !== 'admin'){
            return $this->Unauthorized("You are not authorized to activate users.");
        }

        $validator = validator()->make(request()->all(), [
            "user_ids" => "required|array",
            "user_ids.*" => "exists:users,id"
        ]);    

        if($validator->fails()){
            return $this->BadRequest($validator);
        }

        $users = User::whereIn('id', $validator->validated()['user_ids'])->get();
        if($users->isEmpty()){
            return $this->NotFound("One or more users not found!");
        }

        foreach($users as $user){
            $user->is_active = true;
            $user->save();
        }

        return $this->Success($users, "Users activated!");
    }
}