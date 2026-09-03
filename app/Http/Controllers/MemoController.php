<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Memo;
use App\Models\User;

class MemoController extends Controller
{
    public function index(){

        $role = auth()->user()?->role;
        if(!auth()->user() || !in_array($role, ['admin', 'principal', 'teacher', 'student'])){
            return $this->Unauthorized("You are not authorized to view memos.");
        }

        if(in_array($role, ['admin', 'principal'])){
            return $this->Success(Memo::all());
        }

        $memos = Memo::whereHas('users', function ($query) {
            $query->where('users.id', auth()->id());
        })->get();
        return $this->Success($memos);
    }

    public function show($id){
        $role = auth()->user()?->role;

        if (!auth()->user() || !in_array($role, ['admin', 'principal', 'teacher'])) {
            return $this->Unauthorized("You are not authorized to view this memo.");
        }

        $memo = Memo::find($id);
        if (!$memo) {
            return $this->NotFound("Memo not found!");
        }

        if (in_array($role, ['admin', 'principal'])) {
            return $this->Success($memo);
        }

        // Check if the current user is a recipient
        $isRecipient = $memo->users()->where('users.id', auth()->id())->exists();

        if (!$isRecipient) {
            return $this->Forbidden("You do not have permission to view this memo.");
        }

        return $this->Success($memo);
    }

    public function store(){

        if(!auth()->user() || auth()->user()->role !== 'principal'){
            return $this->Unauthorized("You are not authorized to create a memo.");
        }

        $validator = validator()->make(request()->all(), [
            "title" => "required|string|max:255",
            "content" => "required|string|max:32000",
            "category" => "required|string|max:255",
        ]);

        if($validator->fails()){
            return $this->BadRequest($validator);
        }

        $memo = Memo::create($validator->validated());
        
        $users = User::where('role', 'teacher')->pluck('id')->toArray();

        $memo->users()->attach($users);
        
        return $this->Created($memo);
    }

    public function destroy($id){
        if(!auth()->user() || (auth()->user()->role !== 'principal' && auth()->user()->role !== 'admin')){
            return $this->Unauthorized("You are not authorized to delete a memo.");
        }


        $memo = Memo::find($id);
        if(!$memo){
            return $this->NotFound("Memo not found!");
        }

        $memo->delete();
        return $this->Success(null, "Memo deleted!");
    }
}
