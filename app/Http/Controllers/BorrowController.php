<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Borrow;
use App\Models\Book;
use Illuminate\Support\Facades\DB;

class BorrowController extends Controller
{
    public function index(){
        if(!auth()->user()) return $this->Unauthorized();

        if(auth()->user()->role === 'admin'){
            return $this->Success(Borrow::with(['student','book'])->get());
        }
        $borrows = Borrow::with('book')
            ->where('student_id', auth()->user()->student->id ?? null)
            ->get();
        return $this->Success($borrows);
    }

    public function show($id){
        $borrow = Borrow::with(['student','book'])->find($id);
        if(!$borrow) return $this->NotFound("Borrow record not found!");

        $isOwner = auth()->user()->student && $borrow->student_id === auth()->user()->student->id;
        if(auth()->user()->role !== 'admin' && !$isOwner){
            return $this->Forbidden("You do not have permission to view this record.");
        }

        return $this->Success($borrow);
    }

    public function store(Request $request){
        if(!auth()->user() || auth()->user()->role !== 'admin'){
            return $this->Unauthorized("You are not authorized to check out a book.");
        }

        $validator = validator()->make($request->all(), [
            "student_id" => "required|exists:students,id",
            "book_id" => "required|exists:books,id",
            "due_date" => "required|date|after:today",
        ]);

        if($validator->fails()) return $this->BadRequest($validator);

        $data = $validator->validated();
        $book = Book::find($data['book_id']);

        if(!$book->is_available){
            return response(["success" => false, "message" => "This book is currently unavailable."], 409);
        }

        $borrow = DB::transaction(function () use ($data, $book) {
            $book->update(['is_available' => false]);

            return Borrow::create([
                'student_id' => $data['student_id'],
                'book_id' => $data['book_id'],
                'borrow_date' => now()->toDateString(),
                'due_date' => $data['due_date'],
                'status' => 'borrowed',
            ]);
        });

        return $this->Created($borrow);
    }

    public function returnBook($id){
        if(!auth()->user() || auth()->user()->role !== 'admin'){
            return $this->Unauthorized("You are not authorized to process a return.");
        }

        $borrow = Borrow::find($id);
        if(!$borrow) return $this->NotFound("Borrow record not found!");
        if($borrow->status === 'returned'){
            return response(["success" => false, "message" => "This book has already been returned."], 409);
        }

        DB::transaction(function () use ($borrow){
            $borrow->update([
                'return_date' => now()->toDateString(),
                'status' => 'returned',
            ]);
            $borrow->book()->update(['is_available' => true]);
        });

        return $this->Success($borrow, "Book returned!");
    }
}