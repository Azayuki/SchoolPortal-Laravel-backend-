```markdown
# School Library Management API

A RESTful API built with **Laravel** and **Laravel Sanctum** for managing a school library system.  
It supports multiple user roles, book borrowing, reservations, student enrollment, and internal memos.

---

## Features

### Authentication
- Student registration
- Employee registration (Admin / Teacher / Principal) — accounts start as **inactive**
- Login with email & password
- Logout
- Auth check
- Inactive employees cannot log in until activated by an admin

### User Management
- Full CRUD for users (Admin only)
- View inactive users
- Bulk activate employees
- Get current authenticated user (`/users/me`)

### Students
- View all students
- View single student

### Sections
- CRUD for class sections
- Each section is assigned to a teacher

### Books
- CRUD for books
- SKU uniqueness rule: the same SKU can only be used for the **exact same book** (same title + author)
- Track availability (`is_available`)

### Borrowing
- Admin can check out books to students
- Admin can process book returns
- Students can view their own borrow history
- Admins can view all borrow records

### Reservations
- Students can reserve unavailable books
- Students can cancel their own reservations
- Admins can view/manage all reservations

### Enrollments
- Track student enrollment status (`enrolled`, `withdrawn`, `graduated`)
- Year-based enrollment records

### Memos
- Principals can create memos
- Memos are automatically sent to all teachers
- Teachers can only view memos they received
- Admins and Principals can view/delete memos

---

## Tech Stack

- PHP 8.x
- Laravel 11
- Laravel Sanctum (API authentication)
- MySQL / MariaDB

---

## Installation

1. Clone the repository
```bash
git clone <repository-url>
cd <project-folder>
```

2. Install dependencies

```bash
composer install
```

3. Copy environment file

```bash
cp .env.example .env
```

4. Generate application key

```bash
php artisan key:generate
```

5. Configure your database in `.env`
6. Run migrations

```bash
php artisan migrate
```

7. Start the development server

```bash
php artisan serve
```

---

## Authentication

All protected routes require a Bearer token:

```
Authorization: Bearer {token}
```

You receive the token after a successful login.

---

## API Endpoints

### Auth (Public)

| Method | Endpoint               | Description                      |
| ------ | ---------------------- | -------------------------------- |
| POST   | `/register/student`  | Register a new student           |
| POST   | `/register/employee` | Register admin/teacher/principal |
| POST   | `/login`             | Login                            |

### Auth (Protected)

| Method | Endpoint        | Description            |
| ------ | --------------- | ---------------------- |
| POST   | `/logout`     | Logout                 |
| GET    | `/check-auth` | Check if authenticated |

### Users

| Method | Endpoint            | Description         | Role  |
| ------ | ------------------- | ------------------- | ----- |
| GET    | `/users`          | List all users      | Admin |
| POST   | `/users`          | Create user         | Admin |
| GET    | `/users/me`       | Get current user    | Any   |
| GET    | `/users/inactive` | List inactive users | Admin |
| POST   | `/users/activate` | Bulk activate users | Admin |
| GET    | `/users/{id}`     | Get user            | Admin |
| PUT    | `/users/{id}`     | Update user         | Admin |
| DELETE | `/users/{id}`     | Delete user         | Admin |

### Students

| Method | Endpoint           | Description   | Role  |
| ------ | ------------------ | ------------- | ----- |
| GET    | `/students`      | List students | Admin |
| GET    | `/students/{id}` | Get student   | Admin |

### Sections

| Method | Endpoint           | Description    | Role           |
| ------ | ------------------ | -------------- | -------------- |
| GET    | `/sections`      | List sections  | Admin          |
| GET    | `/sections/{id}` | Get section    | Admin, Teacher |
| POST   | `/sections`      | Create section | Admin          |
| PUT    | `/sections/{id}` | Update section | Admin          |
| DELETE | `/sections/{id}` | Delete section | Admin          |

### Books

| Method | Endpoint        | Description | Role  |
| ------ | --------------- | ----------- | ----- |
| GET    | `/books`      | List books  | Any   |
| GET    | `/books/{id}` | Get book    | Any   |
| POST   | `/books`      | Create book | Admin |
| PUT    | `/books/{id}` | Update book | Admin |
| DELETE | `/books/{id}` | Delete book | Admin |

### Borrows

| Method | Endpoint                 | Description       | Role                  |
| ------ | ------------------------ | ----------------- | --------------------- |
| GET    | `/borrows`             | List borrows      | Admin / Student (own) |
| GET    | `/borrows/{id}`        | Get borrow record | Admin / Owner         |
| POST   | `/borrows`             | Check out a book  | Admin                 |
| POST   | `/borrows/{id}/return` | Return a book     | Admin                 |

### Reservations

| Method | Endpoint               | Description        | Role                  |
| ------ | ---------------------- | ------------------ | --------------------- |
| GET    | `/reservations`      | List reservations  | Admin / Student (own) |
| GET    | `/reservations/{id}` | Get reservation    | Admin / Owner         |
| POST   | `/reservations`      | Create reservation | Student               |
| DELETE | `/reservations/{id}` | Cancel reservation | Admin / Owner         |

### Enrollments

| Method | Endpoint              | Description       | Role  |
| ------ | --------------------- | ----------------- | ----- |
| GET    | `/enrollments`      | List enrollments  | Admin |
| GET    | `/enrollments/{id}` | Get enrollment    | Admin |
| POST   | `/enrollments`      | Create enrollment | Admin |
| PUT    | `/enrollments/{id}` | Update enrollment | Admin |
| DELETE | `/enrollments/{id}` | Delete enrollment | Admin |

### Memos

| Method | Endpoint        | Description | Role                               |
| ------ | --------------- | ----------- | ---------------------------------- |
| GET    | `/memos`      | List memos  | Admin, Principal, Teacher, Student |
| GET    | `/memos/{id}` | Get memo    | Admin, Principal, Teacher          |
| POST   | `/memos`      | Create memo | Principal                          |
| DELETE | `/memos/{id}` | Delete memo | Admin, Principal                   |

---

## User Roles

| Role                | Permissions                                     |
| ------------------- | ----------------------------------------------- |
| **Admin**     | Full access to almost everything                |
| **Principal** | Create/delete memos, view most data             |
| **Teacher**   | View sections, view received memos              |
| **Student**   | View books, borrow history, create reservations |

---

## Response Format

### Success

```json
{
  "success": true,
  "data": {},
  "message": "Succeeded!",
  "others": []
}
```

### Error

```json
{
  "success": false,
  "message": "Error message",
  "errors": {}
}
```

---

## Notes

- Employee accounts are created with `is_active = false` and must be activated by an admin before they can log in.
- When a book is returned, its `is_available` flag is set back to `true`.
- Memos created by a principal are automatically attached to all users with the `teacher` role.

```

You can save this as `README.md` in the root of your project.
```

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
