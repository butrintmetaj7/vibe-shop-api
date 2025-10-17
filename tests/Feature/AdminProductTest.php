<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin can view product listing.
     */
    public function test_admin_can_view_product_listing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/admin/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'external_id',
                        'title',
                        'description',
                        'price',
                        'category',
                        'image',
                        'rating' => ['rate', 'count'],
                        'created_at',
                        'updated_at',
                    ],
                ],
                'pagination',
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Data retrieved successfully',
            ]);

        // Verify admin resource includes internal fields
        $products = $response->json('data');
        $this->assertArrayHasKey('external_id', $products[0]);
        $this->assertArrayHasKey('created_at', $products[0]);
        $this->assertArrayHasKey('updated_at', $products[0]);
    }

    /**
     * Test that admin can create a product.
     */
    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $productData = [
            'title' => 'New Test Product',
            'description' => 'This is a test product description',
            'price' => 99.99,
            'category' => 'electronics',
            'image' => 'https://example.com/image.jpg',
            'rating_rate' => 4.5,
            'rating_count' => 100,
        ];

        $response = $this->postJson('/api/v1/admin/products', $productData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => [
                    'title' => 'New Test Product',
                    'price' => 99.99,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'title' => 'New Test Product',
            'price' => 99.99,
        ]);
    }

    /**
     * Test that admin can update a product.
     */
    public function test_admin_can_update_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $product = Product::factory()->create([
            'title' => 'Original Title',
            'price' => 50.00,
        ]);

        $updateData = [
            'title' => 'Updated Title',
            'price' => 75.00,
        ];

        $response = $this->putJson("/api/v1/admin/products/{$product->id}", $updateData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => [
                    'title' => 'Updated Title',
                    'price' => 75.00,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'title' => 'Updated Title',
            'price' => 75.00,
        ]);
    }

    /**
     * Test that admin can delete a product.
     */
    public function test_admin_can_delete_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $product = Product::factory()->create();

        $response = $this->deleteJson("/api/v1/admin/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product deleted successfully',
            ]);

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /**
     * Test that admin can view a single product.
     */
    public function test_admin_can_view_single_product(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/admin/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'id' => $product->id,
                    'title' => $product->title,
                ],
            ])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'external_id',
                    'title',
                    'description',
                    'price',
                    'category',
                    'image',
                    'rating' => ['rate', 'count'],
                    'created_at',
                    'updated_at',
                ],
                'message',
            ]);

        // Verify admin resource includes internal fields
        $this->assertArrayHasKey('external_id', $response->json('data'));
        $this->assertArrayHasKey('created_at', $response->json('data'));
        $this->assertArrayHasKey('updated_at', $response->json('data'));
    }

    /**
     * Test that non-admin user cannot access admin endpoints.
     */
    public function test_non_admin_cannot_access_admin_endpoints(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        Sanctum::actingAs($user);

        $product = Product::factory()->create();

        // Test GET index
        $response = $this->getJson('/api/v1/admin/products');
        $response->assertStatus(403);

        // Test POST store
        $response = $this->postJson('/api/v1/admin/products', [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category' => 'electronics',
        ]);
        $response->assertStatus(403);

        // Test PUT update
        $response = $this->putJson("/api/v1/admin/products/{$product->id}", [
            'title' => 'Updated Title',
        ]);
        $response->assertStatus(403);

        // Test DELETE destroy
        $response = $this->deleteJson("/api/v1/admin/products/{$product->id}");
        $response->assertStatus(403);
    }

    /**
     * Test that unauthenticated user cannot access admin endpoints.
     */
    public function test_unauthenticated_user_cannot_access_admin_endpoints(): void
    {
        $product = Product::factory()->create();

        // Test GET index
        $response = $this->getJson('/api/v1/admin/products');
        $response->assertStatus(401);

        // Test POST store
        $response = $this->postJson('/api/v1/admin/products', [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category' => 'electronics',
        ]);
        $response->assertStatus(401);

        // Test PUT update
        $response = $this->putJson("/api/v1/admin/products/{$product->id}", [
            'title' => 'Updated Title',
        ]);
        $response->assertStatus(401);

        // Test DELETE destroy
        $response = $this->deleteJson("/api/v1/admin/products/{$product->id}");
        $response->assertStatus(401);
    }

    /**
     * Test validation errors on invalid product data.
     */
    public function test_validation_errors_on_invalid_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin);

        // Test missing required fields
        $response = $this->postJson('/api/v1/admin/products', []);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'description', 'price', 'category']);

        // Test invalid price
        $response = $this->postJson('/api/v1/admin/products', [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'price' => -10,
            'category' => 'electronics',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['price']);

        // Test invalid rating_rate
        $response = $this->postJson('/api/v1/admin/products', [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category' => 'electronics',
            'rating_rate' => 10,
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['rating_rate']);

        // Test invalid URL for image
        $response = $this->postJson('/api/v1/admin/products', [
            'title' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category' => 'electronics',
            'image' => 'not-a-valid-url',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image']);
    }
}
