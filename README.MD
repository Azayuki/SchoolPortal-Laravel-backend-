```markdown
```markdown
## Testing

### Prerequisites

#### Linux
```bash
sudo apt update
sudo apt install php-sqlite3
```

#### Windows (Laragon)

SQLite is usually already enabled.
If tests fail with `could not find driver`, open:

```
Laragon → Menu → PHP → php.ini
```

Uncomment this line:

```ini
extension=pdo_sqlite
extension=sqlite3
```

Then restart Laragon.

---

### Run the test suite

#### Linux / Windows (Laragon terminal)

```bash
# Run all tests
php artisan test

# Run only the API feature tests
php artisan test --filter=ApiTest

# Run with detailed output
php artisan test --filter=ApiTest --testdox
```

---

### Seed the database (for manual testing)

```bash
php artisan migrate:fresh --seed
```

This creates:

| Role             | Username             | Password        | Active |
| ---------------- | -------------------- | --------------- | ------ |
| Admin            | `admin`            | `password123` | Yes    |
| Principal        | `principal`        | `password123` | Yes    |
| Teacher          | `teacher`          | `password123` | Yes    |
| Inactive Teacher | `inactive_teacher` | `password123` | No     |
| Student          | `student1`         | `password123` | Yes    |

Also seeds sample sections and books.

---

### Test database notes

- Feature tests use **SQLite in-memory** by default (`RefreshDatabase`).
- No need to configure a separate MySQL/MariaDB test database.
- Each test runs on a clean database.

---

### Example login for manual API testing

```json
POST /api/login
{
  "username": "admin",
  "password": "password123"
}
```

```

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
