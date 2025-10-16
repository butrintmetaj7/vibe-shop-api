<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test isAdmin() method returns true for admin users.
     */
    public function test_is_admin_returns_true_for_admin_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isCustomer());
    }

    /**
     * Test isAdmin() method returns false for customer users.
     */
    public function test_is_admin_returns_false_for_customer_role(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->assertFalse($customer->isAdmin());
        $this->assertTrue($customer->isCustomer());
    }

    /**
     * Test isCustomer() method returns true for customer users.
     */
    public function test_is_customer_returns_true_for_customer_role(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->assertTrue($customer->isCustomer());
        $this->assertFalse($customer->isAdmin());
    }

    /**
     * Test isCustomer() method returns false for admin users.
     */
    public function test_is_customer_returns_false_for_admin_role(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertFalse($admin->isCustomer());
        $this->assertTrue($admin->isAdmin());
    }

    /**
     * Test user has correct fillable attributes.
     */
    public function test_user_has_correct_fillable_attributes(): void
    {
        $user = new User();

        $this->assertEquals(
            ['name', 'email', 'password', 'role'],
            $user->getFillable()
        );
    }

    /**
     * Test user has correct hidden attributes.
     */
    public function test_user_has_correct_hidden_attributes(): void
    {
        $user = new User();

        $this->assertEquals(
            ['password', 'remember_token'],
            $user->getHidden()
        );
    }

    /**
     * Test password is automatically hashed.
     */
    public function test_password_is_automatically_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plain_password',
        ]);

        $this->assertNotEquals('plain_password', $user->password);
        $this->assertTrue(password_verify('plain_password', $user->password));
    }

    /**
     * Test default role is customer.
     */
    public function test_default_role_is_customer(): void
    {
        $user = User::factory()->create();

        $this->assertEquals('customer', $user->role);
    }

    /**
     * Test user can be created with admin role.
     */
    public function test_user_can_be_created_with_admin_role(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertEquals('admin', $user->role);
    }

    /**
     * Test user has HasApiTokens trait.
     */
    public function test_user_has_api_tokens_trait(): void
    {
        $user = User::factory()->create();

        $this->assertTrue(method_exists($user, 'createToken'));
        $this->assertTrue(method_exists($user, 'tokens'));
    }

    /**
     * Test user can create tokens.
     */
    public function test_user_can_create_tokens(): void
    {
        $user = User::factory()->create();

        $token = $user->createToken('test_token');

        $this->assertNotNull($token);
        $this->assertNotNull($token->plainTextToken);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'name' => 'test_token',
        ]);
    }
}

