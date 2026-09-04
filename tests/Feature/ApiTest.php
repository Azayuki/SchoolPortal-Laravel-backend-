<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Section;
use App\Models\Book;
use App\Models\Borrow;
use App\Models\Enrollment;
use App\Models\Memo;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teacher;
    protected User $principal;
    protected User $studentUser;
    protected Student $student;
    protected Section $section;
    protected Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Users first (no foreign keys)
        $this->admin = User::create([
            'username'  => 'admin_user',
            'email'     => 'admin@school.com',
            'password'  => 'password123',
            'role'      => 'admin',
            'is_active' => true,
        ]);

        $this->teacher = User::create([
            'username'  => 'teacher_user',
            'email'     => 'teacher@school.com',
            'password'  => 'password123',
            'role'      => 'teacher',
            'is_active' => true,
        ]);

        $this->principal = User::create([
            'username'  => 'principal_user',
            'email'     => 'principal@school.com',
            'password'  => 'password123',
            'role'      => 'principal',
            'is_active' => true,
        ]);

        $this->studentUser = User::create([
            'username'  => 'student_user',
            'email'     => 'student@school.com',
            'password'  => 'password123',
            'role'      => 'student',
            'is_active' => true,
        ]);

        // 2. Section (needs teacher user_id)
        $this->section = Section::create([
            'name'        => 'Grade 10 - Einstein',
            'grade_level' => 10,
            'user_id'     => $this->teacher->id,
        ]);

        // 3. Student (needs section_id + user_id)
        $this->student = Student::create([
            'first_name' => 'Juan',
            'last_name'  => 'Dela Cruz',
            'section_id' => $this->section->id,
            'user_id'    => $this->studentUser->id,
        ]);

        // 4. Book (no foreign keys)
        $this->book = Book::create([
            'SKU'            => 'BK-001',
            'title'          => 'Clean Code',
            'author'         => 'Robert C. Martin',
            'year_published' => 2008,
            'is_available'   => true,
        ]);
    }

    // =========================================================
    // AUTH
    // =========================================================

    public function test_register_student(): void
    {
        $response = $this->postJson('/api/register/student', [
            'username'   => 'new_student',
            'email'      => 'newstudent@school.com',
            'password'   => 'password123',
            'first_name' => 'Maria',
            'last_name'  => 'Santos',
            'section_id' => $this->section->id,
        ]);

        $response->assertStatus(201)->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', ['username' => 'new_student', 'role' => 'student']);
        $this->assertDatabaseHas('students', ['first_name' => 'Maria', 'last_name' => 'Santos']);
    }

    public function test_register_employee(): void
    {
        $response = $this->postJson('/api/register/employee', [
            'username' => 'new_teacher',
            'email'    => 'newteacher@school.com',
            'password' => 'password123',
            'role'     => 'teacher',
        ]);

        $response->assertStatus(201)->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'username'  => 'new_teacher',
            'is_active' => false,
        ]);
    }

    public function test_login_success(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'admin_user',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                 ->assertJson(['success' => true])
                 ->assertJsonStructure(['data' => ['token']]);
    }

    public function test_login_invalid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'admin_user',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)->assertJson(['success' => false]);
    }

    public function test_login_inactive_user(): void
    {
        User::create([
            'username'  => 'inactive_user',
            'email'     => 'inactive@school.com',
            'password'  => 'password123',
            'role'      => 'teacher',
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/login', [
            'username' => 'inactive_user',
            'password' => 'password123',
        ]);

        $response->assertStatus(401)->assertJson(['success' => false]);
    }

    public function test_logout(): void
    {
        $token = $this->admin->createToken('api')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/logout');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_check_auth(): void
    {
        $token = $this->admin->createToken('api')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/check-auth');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    // =========================================================
    // USERS
    // =========================================================

    public function test_list_users(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->getJson('/api/users');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_create_user(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->postJson('/api/users', [
                             'username' => 'created_user',
                             'email'    => 'created@school.com',
                             'password' => 'password123',
                             'role'     => 'teacher',
                         ]);

        $response->assertStatus(201)->assertJson(['success' => true]);
    }

    public function test_get_user(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->getJson("/api/users/{$this->teacher->id}");

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_update_user(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->putJson("/api/users/{$this->teacher->id}", [
                             'username'  => 'updated_teacher',
                             'is_active' => true,
                         ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_delete_user(): void
    {
        $user = User::create([
            'username'  => 'to_delete',
            'email'     => 'delete@school.com',
            'password'  => 'password123',
            'role'      => 'teacher',
            'is_active' => true,
        ]);

        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->deleteJson("/api/users/{$user->id}");

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_get_me(): void
    {
        $response = $this->withToken($this->studentUser->createToken('api')->plainTextToken)
                         ->getJson('/api/users/me');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_list_inactive_users(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->getJson('/api/users/inactive');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_bulk_activate_users(): void
    {
        $inactive = User::create([
            'username'  => 'inactive2',
            'email'     => 'inactive2@school.com',
            'password'  => 'password123',
            'role'      => 'teacher',
            'is_active' => false,
        ]);

        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->postJson('/api/users/activate', [
                             'user_ids' => [$inactive->id],
                         ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'id'        => $inactive->id,
            'is_active' => true,
        ]);
    }

    // =========================================================
    // STUDENTS
    // =========================================================

    public function test_list_students(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->getJson('/api/students');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_get_student(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->getJson("/api/students/{$this->student->id}");

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    // =========================================================
    // SECTIONS
    // =========================================================

    public function test_list_sections(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->getJson('/api/sections');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_get_section(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->getJson("/api/sections/{$this->section->id}");

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_create_section(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->postJson('/api/sections', [
                             'name'        => 'Grade 11 - Curie',
                             'grade_level' => 11,
                             'user_id'     => $this->teacher->id,
                         ]);

        $response->assertStatus(201)->assertJson(['success' => true]);
    }

    public function test_update_section(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->putJson("/api/sections/{$this->section->id}", [
                             'name' => 'Grade 10 - Newton',
                         ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_delete_section(): void
    {
        $section = Section::create([
            'name'        => 'Temp Section',
            'grade_level' => 9,
            'user_id'     => $this->teacher->id,
        ]);

        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->deleteJson("/api/sections/{$section->id}");

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    // =========================================================
    // BOOKS
    // =========================================================

    public function test_list_books(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->getJson('/api/books');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_get_book(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->getJson("/api/books/{$this->book->id}");

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_create_book(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->postJson('/api/books', [
                             'SKU'            => 'BK-002',
                             'title'          => 'The Pragmatic Programmer',
                             'author'         => 'Andy Hunt',
                             'year_published' => 1999,
                         ]);

        $response->assertStatus(201)->assertJson(['success' => true]);
    }

    public function test_update_book(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->putJson("/api/books/{$this->book->id}", [
                             'title' => 'Clean Code (Updated)',
                         ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_delete_book(): void
    {
        $book = Book::create([
            'SKU'            => 'BK-999',
            'title'          => 'Temp Book',
            'author'         => 'Temp Author',
            'year_published' => 2020,
            'is_available'   => true,
        ]);

        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->deleteJson("/api/books/{$book->id}");

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    // =========================================================
    // BORROWS
    // =========================================================

    public function test_create_borrow(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->postJson('/api/borrows', [
                             'student_id' => $this->student->id,
                             'book_id'    => $this->book->id,
                             'due_date'   => now()->addDays(14)->toDateString(),
                         ]);

        $response->assertStatus(201)->assertJson(['success' => true]);

        $this->assertDatabaseHas('books', [
            'id'           => $this->book->id,
            'is_available' => false,
        ]);
    }

    public function test_list_borrows(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->getJson('/api/borrows');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_return_book(): void
    {
        $this->book->update(['is_available' => false]);

        $borrow = Borrow::create([
            'student_id'  => $this->student->id,
            'book_id'     => $this->book->id,
            'borrow_date' => now()->toDateString(),
            'due_date'    => now()->addDays(14)->toDateString(),
            'status'      => 'borrowed',
        ]);

        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->postJson("/api/borrows/{$borrow->id}/return");

        $response->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('books', [
            'id'           => $this->book->id,
            'is_available' => true,
        ]);
    }

    // =========================================================
    // RESERVATIONS
    // =========================================================

    public function test_create_reservation(): void
    {
        $this->book->update(['is_available' => false]);

        $response = $this->withToken($this->studentUser->createToken('api')->plainTextToken)
                         ->postJson('/api/reservations', [
                             'book_id'         => $this->book->id,
                             'expiration_date' => now()->addDays(7)->toDateString(),
                         ]);

        $response->assertStatus(201)->assertJson(['success' => true]);
    }

    public function test_list_reservations(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->getJson('/api/reservations');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_cancel_reservation(): void
    {
        $reservation = Reservation::create([
            'student_id'       => $this->student->id,
            'book_id'          => $this->book->id,
            'reservation_date' => now()->toDateString(),
            'expiration_date'  => now()->addDays(7)->toDateString(),
            'status'           => 'pending',
        ]);

        $response = $this->withToken($this->studentUser->createToken('api')->plainTextToken)
                         ->deleteJson("/api/reservations/{$reservation->id}");

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    // =========================================================
    // ENROLLMENTS
    // =========================================================

    public function test_create_enrollment(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->postJson('/api/enrollments', [
                             'student_id' => $this->student->id,
                             'start_year' => 2025,
                             'end_year'   => 2026,
                             'status'     => 'enrolled',
                         ]);

        $response->assertStatus(201)->assertJson(['success' => true]);
    }

    public function test_list_enrollments(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->getJson('/api/enrollments');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_update_enrollment(): void
    {
        $enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'start_year' => 2025,
            'end_year'   => 2026,
            'status'     => 'enrolled',
        ]);

        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->putJson("/api/enrollments/{$enrollment->id}", [
                             'status' => 'graduated',
                         ]);

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_delete_enrollment(): void
    {
        $enrollment = Enrollment::create([
            'student_id' => $this->student->id,
            'start_year' => 2024,
            'end_year'   => 2025,
            'status'     => 'enrolled',
        ]);

        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->deleteJson("/api/enrollments/{$enrollment->id}");

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    // =========================================================
    // MEMOS
    // =========================================================

    public function test_create_memo(): void
    {
        $response = $this->withToken($this->principal->createToken('api')->plainTextToken)
                         ->postJson('/api/memos', [
                             'title'    => 'Faculty Meeting',
                             'content'  => 'Meeting on Friday at 3PM.',
                             'category' => 'Announcement',
                         ]);

        $response->assertStatus(201)->assertJson(['success' => true]);
    }

    public function test_list_memos(): void
    {
        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->getJson('/api/memos');

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_get_memo(): void
    {
        $memo = Memo::create([
            'title'    => 'Test Memo',
            'content'  => 'Test content',
            'category' => 'General',
        ]);

        $response = $this->withToken($this->admin->createToken('api')->plainTextToken)
                         ->getJson("/api/memos/{$memo->id}");

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_delete_memo(): void
    {
        $memo = Memo::create([
            'title'    => 'Delete Me',
            'content'  => 'To be deleted',
            'category' => 'Temp',
        ]);

        $response = $this->withToken($this->principal->createToken('api')->plainTextToken)
                         ->deleteJson("/api/memos/{$memo->id}");

        $response->assertStatus(200)->assertJson(['success' => true]);
    }

    // =========================================================
    // AUTHORIZATION
    // =========================================================

    public function test_student_cannot_create_book(): void
    {
        $response = $this->withToken($this->studentUser->createToken('api')->plainTextToken)
                         ->postJson('/api/books', [
                             'SKU'            => 'BK-999',
                             'title'          => 'Unauthorized Book',
                             'author'         => 'Nobody',
                             'year_published' => 2020,
                         ]);

        $response->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_protected_routes(): void
    {
        $response = $this->getJson('/api/books');
        $response->assertStatus(401);
    }
}