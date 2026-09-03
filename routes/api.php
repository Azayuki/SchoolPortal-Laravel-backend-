<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\BorrowController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\MemoController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\UserController;

// ======================
// AUTH (Public)
// ======================
Route::post('/register/student', [AuthController::class, 'registerAsStudent']);
Route::post('/register/employee', [AuthController::class, 'registerAsEmployee']);
Route::post('/login', [AuthController::class, 'login']);

// ======================
// Protected routes
// ======================
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/check-auth', [AuthController::class, 'checkAuth']);

    // Users
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/me', [UserController::class, 'me']);
    Route::get('/users/inactive', [UserController::class, 'showInactiveUsers']);
    Route::post('/users/activate', [UserController::class, 'bulkActivateUsers']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Students
    Route::get('/students', [StudentController::class, 'index']);
    Route::get('/students/{id}', [StudentController::class, 'show']);

    // Sections
    Route::get('/sections', [SectionController::class, 'index']);
    Route::get('/sections/{id}', [SectionController::class, 'show']);
    Route::post('/sections', [SectionController::class, 'store']);
    Route::put('/sections/{id}', [SectionController::class, 'update']);
    Route::delete('/sections/{id}', [SectionController::class, 'destroy']);

    // Books
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{id}', [BookController::class, 'show']);
    Route::post('/books', [BookController::class, 'store']);
    Route::put('/books/{id}', [BookController::class, 'update']);
    Route::delete('/books/{id}', [BookController::class, 'destroy']);

    // Borrows
    Route::get('/borrows', [BorrowController::class, 'index']);
    Route::get('/borrows/{id}', [BorrowController::class, 'show']);
    Route::post('/borrows', [BorrowController::class, 'store']);
    Route::post('/borrows/{id}/return', [BorrowController::class, 'returnBook']);

    // Reservations
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/reservations/{id}', [ReservationController::class, 'show']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::delete('/reservations/{id}', [ReservationController::class, 'cancel']);

    // Enrollments
    Route::get('/enrollments', [EnrollmentController::class, 'index']);
    Route::get('/enrollments/{id}', [EnrollmentController::class, 'show']);
    Route::post('/enrollments', [EnrollmentController::class, 'store']);
    Route::put('/enrollments/{id}', [EnrollmentController::class, 'update']);
    Route::delete('/enrollments/{id}', [EnrollmentController::class, 'destroy']);

    // Memos
    Route::get('/memos', [MemoController::class, 'index']);
    Route::get('/memos/{id}', [MemoController::class, 'show']);
    Route::post('/memos', [MemoController::class, 'store']);
    Route::delete('/memos/{id}', [MemoController::class, 'destroy']);
});