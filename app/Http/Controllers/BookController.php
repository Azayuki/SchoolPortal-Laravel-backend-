<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class BookController extends Controller
{
    public function index(){

        if(!auth()->user()){
            return $this->Unauthorized("You are not authorized to view books.");
        }

        $books = Book::all();
        return $this->Success($books);
    }

    public function show($id){

        if(!auth()->user()){
            return $this->Unauthorized("You are not authorized to view this book.");
        }

        $book = Book::find($id);
        if(!$book){
            return $this->NotFound("Book not found!");
        }
        return $this->Success($book);
    }

    public function store(){
        if (!auth()->user() || auth()->user()->role !== 'admin') {
            return $this->Unauthorized("You are not authorized to create a book.");
        }

        $validator = validator()->make(request()->all(), [
            "SKU"            => "required|string|max:255",
            "title"          => "required|string|max:255",
            "author"         => "required|string|max:255",
            "year_published" => "required|integer|min:0|max:" . date("Y"),
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }

        $data = $validator->validated();

        $conflict = Book::where('SKU', $data['SKU'])
            ->where(function ($query) use ($data) {
                $query->where('title', '!=', $data['title'])
                    ->orWhere('author', '!=', $data['author']);
            })
            ->exists();

        if ($conflict) {
            return $this->BadRequest($validator, "Different book already has this SKU!");
        }

        $book = Book::create($data);
        return $this->Created($book);
    }

    public function update($id){

        if (!auth()->user() || auth()->user()->role !== 'admin') {
            return $this->Unauthorized("You are not authorized to update this book.");
        }

        $book = Book::find($id);
        if (!$book) {
            return $this->NotFound("Book not found!");
        }

        $validator = validator()->make(request()->all(), [
            "SKU"            => "sometimes|string|max:255",
            "title"          => "sometimes|string|max:255",
            "author"         => "sometimes|string|max:255",
            "year_published" => "sometimes|integer|min:0|max:" . date("Y"),
        ]);

        if ($validator->fails()) {
            return $this->BadRequest($validator);
        }

        $data = $validator->validated();

        $finalSku    = $data['SKU']    ?? $book->SKU;
        $finalTitle  = $data['title']  ?? $book->title;
        $finalAuthor = $data['author'] ?? $book->author;

        $conflict = Book::where('SKU', $finalSku)
            ->where('id', '!=', $id)
            ->where(function ($query) use ($finalTitle, $finalAuthor) {
                $query->where('title', '!=', $finalTitle)
                    ->orWhere('author', '!=', $finalAuthor);
            })
            ->exists();

        if ($conflict) {
            return $this->BadRequest($validator, "Different book already has this SKU!");
        }

        $book->update($data);
        return $this->Success($book, "Book updated!");
    }

    public function destroy($id){
        if (!auth()->user() || auth()->user()->role !== 'admin') {
            return $this->Unauthorized("You are not authorized to delete this book.");
        }
        $book = Book::find($id);
        if(!$book){
            return $this->NotFound("Book not found!");
        }

        $book->delete();
        return $this->Success(null, "Book deleted!");
    }
}
