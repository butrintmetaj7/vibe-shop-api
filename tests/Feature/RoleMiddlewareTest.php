<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test routes for role middleware testing
        Route::middleware(['auth:sanctum', 'role:admin'])->get('/api/v1/test/admin-only', function () {
            return response()->json(['message' => 'Admin access granted']);
        });

        Route::middleware(['auth:sanctum', 'role:customer'])->get('/api/v1/test/customer-only', function () {
            return response()->json(['message' => 'Customer access granted']);
        });

        Route::middleware(['auth:sanctum', 'role:admin,customer'])->get('/api/v1/test/both-roles', function () {
            return response()->json(['message' => 'Access granted']);
        });
    }

    /**
     * Test admin user can access admin-only route.
     */
    public function test_admin_can_access_admin_only_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/test/admin-only');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Admin access granted']);
    }

    /**
     * Test customer user cannot access admin-only route.
     */
    public function test_customer_cannot_access_admin_only_route(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/test/admin-only');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden. You do not have the required role.',
            ]);
    }

    /**
     * Test customer user can access customer-only route.
     */
    public function test_customer_can_access_customer_only_route(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/test/customer-only');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Customer access granted']);
    }

    /**
     * Test admin user cannot access customer-only route.
     */
    public function test_admin_cannot_access_customer_only_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/test/customer-only');

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'Forbidden. You do not have the required role.',
            ]);
    }

    /**
     * Test admin user can access route with multiple roles.
     */
    public function test_admin_can_access_multi_role_route(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $token = $admin->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/test/both-roles');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Access granted']);
    }

    /**
     * Test customer user can access route with multiple roles.
     */
    public function test_customer_can_access_multi_role_route(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $token = $customer->createToken('test_token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/test/both-roles');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Access granted']);
    }

    /**
     * Test unauthenticated user cannot access role-protected route.
     */
    public function test_unauthenticated_user_cannot_access_role_protected_route(): void
    {
        $response = $this->getJson('/api/v1/test/admin-only');

        // Sanctum's auth middleware returns Laravel's default response before RoleMiddleware runs
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Test role middleware with invalid token.
     */
    public function test_role_middleware_rejects_invalid_token(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer invalid_token')
            ->getJson('/api/v1/test/admin-only');

        $response->assertStatus(401);
    }
}

