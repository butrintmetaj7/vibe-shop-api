<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminUserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin can list users.
     */
    public function test_admin_can_list_users(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        User::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/admin/users');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'email',
                        'role',
                        'email_verified_at',
                        'created_at',
                        'updated_at',
                    ],
                ],
                'pagination',
            ])
            ->assertJson([
                'success' => true,
            ]);

        // Verify password is not in response
        $users = $response->json('data');
        $this->assertArrayNotHasKey('password', $users[0]);
        $this->assertArrayNotHasKey('remember_token', $users[0]);
    }

    /**
     * Test that admin can list users with pagination.
     */
    public function test_admin_can_list_users_with_pagination(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // Create 20 users (plus the admin = 21 total)
        User::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/admin/users');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pagination' => [
                    'per_page' => 15,
                    'total' => 21,
                    'last_page' => 2,
                    'current_page' => 1,
                ],
            ]);

        $this->assertCount(15, $response->json('data'));
    }

    /**
     * Test that admin can filter users by role.
     */
    public function test_admin_can_filter_users_by_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        User::factory()->count(5)->create(['role' => 'admin']);
        User::factory()->count(10)->create(['role' => 'customer']);

        $response = $this->getJson('/api/v1/admin/users?role=admin');

        $response->assertStatus(200);

        // Should have 6 admins total (initial admin + 5 created)
        $this->assertEquals(6, $response->json('pagination.total'));

        // Verify all returned users are admins
        foreach ($response->json('data') as $user) {
            $this->assertEquals('admin', $user['role']);
        }
    }

    /**
     * Test that admin can search users by name or email.
     */
    public function test_admin_can_search_users_by_name_or_email(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Admin User', 'email' => 'admin@admin.com']);
        Sanctum::actingAs($admin);

        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
        User::factory()->create(['name' => 'Bob Johnson', 'email' => 'bob@test.com']);

        // Search by name
        $response = $this->getJson('/api/v1/admin/users?search=John');
        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('pagination.total')); // John Doe and Bob Johnson

        // Search by email
        $response = $this->getJson('/api/v1/admin/users?search=example.com');
        $response->assertStatus(200);
        $this->assertEquals(2, $response->json('pagination.total')); // john and jane
    }

    /**
     * Test that admin can create user with valid data.
     */
    public function test_admin_can_create_user_with_valid_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
        ];

        $response = $this->postJson('/api/v1/admin/users', $userData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'User created successfully',
                'data' => [
                    'name' => 'Test User',
                    'email' => 'testuser@example.com',
                    'role' => 'customer',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'role' => 'customer',
        ]);

        // Verify password is not in response
        $this->assertArrayNotHasKey('password', $response->json('data'));
    }

    /**
     * Test that admin can view single user.
     */
    public function test_admin_can_view_single_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $response = $this->getJson("/api/v1/admin/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                ],
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'email_verified_at',
                    'created_at',
                    'updated_at',
                ],
            ]);

        // Verify password is not in response
        $this->assertArrayNotHasKey('password', $response->json('data'));
    }

    /**
     * Test that admin can update user.
     */
    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $user = User::factory()->create([
            'name' => 'Original Name',
            'email' => 'original@example.com',
        ]);

        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->putJson("/api/v1/admin/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => [
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /**
     * Test that admin can update user password.
     */
    public function test_admin_can_update_user_password(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $user = User::factory()->create();
        $oldPassword = $user->password;

        $updateData = [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ];

        $response = $this->putJson("/api/v1/admin/users/{$user->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User updated successfully',
            ]);

        // Verify password changed
        $user->refresh();
        $this->assertNotEquals($oldPassword, $user->password);
    }

    /**
     * Test that admin can delete user.
     */
    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $user = User::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/users/{$user->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    /**
     * Test that admin cannot delete themselves.
     */
    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/v1/admin/users/{$admin->id}");

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You cannot delete your own account',
            ]);

        // Verify admin still exists
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    /**
     * Test that admin cannot change own role to customer.
     */
    public function test_admin_cannot_change_own_role_to_customer(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $response = $this->putJson("/api/v1/admin/users/{$admin->id}", [
            'role' => 'customer',
        ]);

        $response->assertStatus(403)
            ->assertJson([
                'success' => false,
                'message' => 'You cannot change your own role',
            ]);

        // Verify role hasn't changed
        $admin->refresh();
        $this->assertEquals('admin', $admin->role);
    }

    /**
     * Test that admin can change other admin's role.
     */
    public function test_admin_can_change_other_admins_role(): void
    {
        $admin1 = User::factory()->create(['role' => 'admin']);
        $admin2 = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin1);

        $response = $this->putJson("/api/v1/admin/users/{$admin2->id}", [
            'role' => 'customer',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => [
                    'role' => 'customer',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $admin2->id,
            'role' => 'customer',
        ]);
    }

    /**
     * Test that non-admin cannot access admin user endpoints.
     */
    public function test_non_admin_cannot_access_admin_user_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        Sanctum::actingAs($user);

        $otherUser = User::factory()->create();

        // Test GET index
        $response = $this->getJson('/api/v1/admin/users');
        $response->assertStatus(403);

        // Test POST store
        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
        ]);
        $response->assertStatus(403);

        // Test GET show
        $response = $this->getJson("/api/v1/admin/users/{$otherUser->id}");
        $response->assertStatus(403);

        // Test PUT update
        $response = $this->putJson("/api/v1/admin/users/{$otherUser->id}", [
            'name' => 'Updated Name',
        ]);
        $response->assertStatus(403);

        // Test DELETE destroy
        $response = $this->deleteJson("/api/v1/admin/users/{$otherUser->id}");
        $response->assertStatus(403);
    }

    /**
     * Test that unauthenticated user cannot access admin user endpoints.
     */
    public function test_unauthenticated_user_cannot_access_admin_user_endpoints(): void
    {
        $user = User::factory()->create();

        // Test GET index
        $response = $this->getJson('/api/v1/admin/users');
        $response->assertStatus(401);

        // Test POST store
        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
        ]);
        $response->assertStatus(401);

        // Test GET show
        $response = $this->getJson("/api/v1/admin/users/{$user->id}");
        $response->assertStatus(401);

        // Test PUT update
        $response = $this->putJson("/api/v1/admin/users/{$user->id}", [
            'name' => 'Updated Name',
        ]);
        $response->assertStatus(401);

        // Test DELETE destroy
        $response = $this->deleteJson("/api/v1/admin/users/{$user->id}");
        $response->assertStatus(401);
    }

    /**
     * Test validation errors on invalid data.
     */
    public function test_validation_errors_on_invalid_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // Test missing required fields
        $response = $this->postJson('/api/v1/admin/users', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password', 'role']);

        // Test invalid email format
        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        // Test password too short
        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
            'role' => 'customer',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);

        // Test invalid role value
        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'invalid-role',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['role']);

        // Test unique email constraint
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'customer',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test password confirmation validation.
     */
    public function test_password_confirmation_validation(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // Test password confirmation mismatch
        $response = $this->postJson('/api/v1/admin/users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'differentpassword',
            'role' => 'customer',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }
}

