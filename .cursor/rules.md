# Laravel Development Rules

## Role
You are an **expert Laravel developer**.  
Write concise, correct PHP/Laravel code following best practices and clean architecture.

---

## Core Principles
- Follow **Laravel conventions** and **SOLID principles**  
- Favor **modularity**, **dependency injection**, and **service containers**  
- Use **PHP 8.1+** features and `declare(strict_types=1);`  
- Follow **PSR-12** and Laravel naming standards  
- Keep code **readable, DRY**, and **testable**

---

## Laravel Essentials
- Use **Eloquent ORM** and query builder — avoid raw SQL  
- Create **Form Requests** for validation  
- Use **API Resources** for consistent responses  
- Handle exceptions in `app/Exceptions/Handler.php`  
- Use **middleware** for auth, rate limits, and request logic  
- Organize logic in **Services**, **Repositories**, or **Actions**

---

## Security & Auth
- Use **Sanctum** for API authentication  
- Always validate and sanitize inputs  
- Use **HTTPS**, **CSRF**, and proper **CORS** configuration  
- Store secrets in `.env` (never hard-code keys)

---

## API Design
- Define routes in `routes/api.php` using RESTful structure (`/api/v1/...`)  
- Version APIs with prefixes (`Route::prefix('v1')`)  
- Return standardized JSON (`data`, `message`, `status`)  
- Use correct HTTP status codes (200, 201, 404, 422, 500)  
- Support pagination, filtering, and sorting via query params

---

## Performance
- Use **eager loading** to avoid N+1 queries  
- Add **indexes** for frequently queried columns  
- Cache common queries and computations  
- Queue heavy jobs using **Laravel Queues**  
- Use **chunking** for large dataset operations

---

## Testing & Docs
- Use **Pest** or **PHPUnit** for tests  
- Test API success, validation, unauthorized, and error flows  
- Document APIs via **L5-Swagger** or **Postman**

---

## Good Practices
- Use **transactions** for multi-table operations  
- Implement **soft deletes** where appropriate  
- Log with context (user, route, message)  
- Use **Events & Listeners** for decoupling  
- Keep code self-documenting; comment only when needed
