# User Management API

A Laravel application for managing user data with gender-based filtering through middleware.

## Features

- **User Table**: Stores user information including name, email, phone, and gender
- **Gender Filtering**: Middleware that filters users by gender (male/female)
- **Factory & Seeder**: Pre-configured database seeding with sample data
- **REST API**: Simple endpoint for retrieving filtered user data

## Getting Started

### Prerequisites
- PHP 8.2+
- Composer
- MySQL/SQLite

### Installation

1. Install dependencies:
```bash
composer install
```

2. Set up environment:
```bash
cp .env.example .env
php artisan key:generate
```

3. Configure database in `.env` and run migrations:
```bash
php artisan migrate:fresh --seed
```

4. Start the development server:
```bash
php artisan serve --port=8001
```

## API Endpoints

### Get Users by Gender

**Request:**
```
GET /api/users?gender=female
GET /api/users?gender=male
```

**Response:**
```json
{
  "middleware": "CheckGenderMiddleware",
  "users": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "gender": "male",
      "phone": "+1234567890"
    }
  ]
}
```

## Database Schema

### Users Table
- `id` - Primary Key
- `name` - User Name
- `email` - Email Address (Unique)
- `gender` - Gender (male/female)
- `phone` - Phone Number
- `password` - Encrypted Password
- `email_verified_at` - Email Verification Timestamp
- `timestamps` - Created/Updated At

## Testing

Use Postman or any HTTP client:

```
GET http://127.0.0.1:8001/api/users?gender=female
GET http://127.0.0.1:8001/api/users?gender=male
```

## Project Structure

```
app/
  Http/
    Controllers/
      UserController.php
    Middleware/
      CheckGenderMiddleware.php
  Models/
    User.php
database/
  factories/
    UserFactory.php
  migrations/
    0001_01_01_000000_create_users_table.php
  seeders/
    DatabaseSeeder.php
routes/
  api.php
```

