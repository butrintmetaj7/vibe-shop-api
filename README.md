# Vibe Shop API

A RESTful API backend for an e-commerce platform built with Laravel 12 and Laravel Sanctum. This API provides comprehensive authentication, role-based access control, and serves as the foundation for a modern e-commerce application.

## 🚀 Features

### Authentication & Authorization
- **Token-based Authentication** using Laravel Sanctum
- **Role-based Access Control** (Admin/Customer roles)
- **User Registration & Login** with validation
- **Secure Token Management** with expiration
- **Rate Limiting** on authentication endpoints

### API Features
- **Versioned API** (`/api/v1/` prefix)
- **Consistent Response Format** using ApiResponse DTO
- **Comprehensive Validation** with Form Requests
- **Strict Type Declarations** throughout codebase
- **Production-ready Security** measures

## 📋 Requirements

- PHP 8.2+
- Composer
- SQLite (default) or MySQL/PostgreSQL
- Laravel 12

## 🛠️ Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/butrintmetaj7/vibe-shop-api.git
   cd vibe-shop-api
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   # For SQLite (default)
   touch database/database.sqlite
   
   # Or configure MySQL/PostgreSQL in .env
   php artisan migrate
   ```

5. **Run tests**
   ```bash
   php artisan test
   ```

## 🔗 API Endpoints

### Authentication Endpoints

| Method | Endpoint | Description | Rate Limit |
|--------|----------|-------------|------------|
| `POST` | `/api/v1/register` | User registration | 10/min |
| `POST` | `/api/v1/login` | User login | 10/min |
| `POST` | `/api/v1/logout` | User logout | - |
| `GET` | `/api/v1/user` | Get user profile | - |

### Request/Response Examples

#### Register User
```bash
POST /api/v1/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "customer"
}
```

**Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "customer",
      "email_verified_at": null,
      "created_at": "2025-10-16T14:30:00.000000Z",
      "updated_at": "2025-10-16T14:30:00.000000Z"
    },
    "token": "1|abc123..."
  }
}
```

#### Login User
```bash
POST /api/v1/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

#### Get User Profile
```bash
GET /api/v1/user
Authorization: Bearer 1|abc123...
```

## 🔒 Security Features

### Rate Limiting
- **Authentication endpoints**: 10 requests per minute per IP
- **Prevents brute force attacks** and API abuse

### Token Security
- **7-day expiration** (configurable via `SANCTUM_TOKEN_EXPIRATION`)
- **Descriptive token names** for audit trails
- **Secure token generation** using Laravel Sanctum

### Data Protection
- **Password hashing** with Laravel's built-in hasher
- **Generic error messages** to prevent user enumeration
- **Strict type declarations** to prevent type coercion bugs

## 🧪 Testing

The project includes comprehensive test coverage:

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test tests/Feature/AuthenticationTest.php
php artisan test tests/Feature/RoleMiddlewareTest.php
php artisan test tests/Unit/UserTest.php

# Run with coverage
php artisan test --coverage
```
## 🏗️ Architecture

### Project Structure
```
app/
├── Http/
│   ├── Controllers/
│   │   └── AuthController.php          # Authentication endpoints
│   ├── Middleware/
│   │   └── RoleMiddleware.php          # Role-based access control
│   ├── Requests/
│   │   ├── LoginRequest.php            # Login validation
│   │   └── RegisterRequest.php         # Registration validation
│   ├── Resources/
│   │   └── UserResource.php            # User data transformation
│   └── Responses/
│       └── ApiResponse.php             # Consistent API responses
├── Models/
│   └── User.php                        # User model with roles
└── ...

tests/
├── Feature/
│   ├── AuthenticationTest.php          # Authentication flow tests
│   └── RoleMiddlewareTest.php          # Role middleware tests
└── Unit/
    └── UserTest.php                    # User model tests
```

## 🔧 Configuration

### Environment Variables
```env
# Authentication
AUTH_GUARD=sanctum
SANCTUM_TOKEN_EXPIRATION=10080  # 7 days in minutes

# Database (SQLite by default)
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

### Sanctum Configuration
- **Token expiration**: 7 days (configurable)
- **Stateful domains**: localhost, 127.0.0.1
- **Guard**: web (for SPA authentication)

## 🚀 Development

### Code Standards
- **PSR-12** coding standards
- **Strict types** (`declare(strict_types=1);`) in all files
- **Laravel best practices** and conventions
- **Comprehensive testing** for all features

### Adding New Features
1. Create feature branch: `git checkout -b feature/your-feature`
2. Implement with tests
3. Follow existing patterns (ApiResponse, validation, etc.)
4. Run tests: `php artisan test`
5. Create pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support

For questions or issues:
- Create an issue on GitHub
- Check the documentation in the `docs/` folder
- Review the test files for usage examples

---

**Built with ❤️ using Laravel 12 and Laravel Sanctum**