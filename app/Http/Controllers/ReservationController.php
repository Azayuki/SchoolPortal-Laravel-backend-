<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reservation;
use App\Models\Book;

class ReservationController extends Controller
{
    public function index(){
        if(!auth()->user()) return $this->Unauthorized();

        if((auth()->user()->role === 'admin' || auth()->user()->role === 'librarian')){
            return $this->Success(Reservation::with(['student','book'])->get());
        }

        $reservations = Reservation::with('book')
            ->where('student_id', auth()->user()->student->id ?? null)
            ->get();
        return $this->Success($reservations);
    }

    public function show($id){
        $reservation = Reservation::with(['student','book'])->find($id);
        if(!$reservation) return $this->NotFound("Reservation not found!");

        $isOwner = auth()->user()->student && $reservation->student_id === auth()->user()->student->id;
        if(auth()->user()->role !== 'admin' && !$isOwner){
            return $this->Forbidden("You do not have permission to view this reservation.");
        }

        return $this->Success($reservation);
    }
    public function store(Request $request){
        if(!auth()->user() || !auth()->user()->student){
            return $this->Unauthorized("Only students can reserve books.");
        }

        $validator = validator()->make($request->all(), [
            "book_id" => "required|exists:books,id",
            "expiration_date" => "required|date|after:today",
        ]);

        if($validator->fails()) return $this->BadRequest($validator);

        $book = Book::find($request->book_id);
        if($book->is_available){
            return response(["success" => false, "message" => "This book is available — borrow it instead of reserving."], 409);
        }

        $studentId = auth()->user()->student->id;

        $alreadyReserved = Reservation::where('book_id', $book->id)
            ->where('student_id', $studentId)
            ->where('status', 'pending')
            ->exists();
        if($alreadyReserved){
            return response(["success" => false, "message" => "You already have a pending reservation for this book."], 409);
        }

        $reservation = Reservation::create([
            'student_id' => $studentId,
            'book_id' => $book->id,
            'reservation_date' => now()->toDateString(),
            'expiration_date' => $request->expiration_date,
            'status' => 'pending',
        ]);

        return $this->Created($reservation);
    }

    public function cancel($id){
        $reservation = Reservation::find($id);
        if(!$reservation) return $this->NotFound("Reservation not found!");

        $isOwner = auth()->user()->student && $reservation->student_id === auth()->user()->student->id;
        if(auth()->user()->role !== 'admin' && !$isOwner){
            return $this->Forbidden();
        }

        $reservation->delete();
        return $this->Success(null, "Reservation cancelled.");
    }
    public static function fulfillNext(Book $book){
        $next = Reservation::where('book_id', $book->id)
            ->where('status', 'pending')
            ->oldest('reservation_date')
            ->first();

        if($next){
            $next->update(['status' => 'borrowed']);
            $book->update(['is_available' => false]);
        }
    }
}