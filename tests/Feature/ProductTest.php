<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that guests can view product listing.
     */
    public function test_guest_can_view_product_listing(): void
    {
        // Create some test products
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'description',
                        'price',
                        'category',
                        'image',
                        'rating' => [
                            'rate',
                            'count',
                        ],
                    ],
                ],
                'pagination' => [
                    'current_page',
                    'per_page',
                    'total',
                    'last_page',
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Data retrieved successfully',
            ]);

        // Verify public resource excludes internal fields
        $products = $response->json('data');
        $this->assertArrayNotHasKey('external_id', $products[0]);
        $this->assertArrayNotHasKey('created_at', $products[0]);
        $this->assertArrayNotHasKey('updated_at', $products[0]);
    }

    /**
     * Test that guests can view a single product.
     */
    public function test_guest_can_view_single_product(): void
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'title',
                    'description',
                    'price',
                    'category',
                    'image',
                    'rating' => [
                        'rate',
                        'count',
                    ],
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Success',
                'data' => [
                    'id' => $product->id,
                    'title' => $product->title,
                ],
            ]);

        // Verify public resource excludes internal fields
        $this->assertArrayNotHasKey('external_id', $response->json('data'));
        $this->assertArrayNotHasKey('created_at', $response->json('data'));
        $this->assertArrayNotHasKey('updated_at', $response->json('data'));
    }

    /**
     * Test product filtering by category.
     */
    public function test_can_filter_products_by_category(): void
    {
        Product::factory()->create(['category' => 'electronics']);
        Product::factory()->create(['category' => 'clothing']);
        Product::factory()->create(['category' => 'electronics']);

        $response = $this->getJson('/api/v1/products?category=electronics');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $products = $response->json('data');
        $this->assertCount(2, $products);

        foreach ($products as $product) {
            $this->assertEquals('electronics', $product['category']);
        }
    }

    /**
     * Test product sorting by price ascending.
     */
    public function test_can_sort_products_by_price_ascending(): void
    {
        Product::factory()->create(['price' => 50.00]);
        Product::factory()->create(['price' => 30.00]);
        Product::factory()->create(['price' => 40.00]);

        $response = $this->getJson('/api/v1/products?sort=price_asc');

        $response->assertStatus(200);

        $products = $response->json('data');
        $this->assertEquals(30.00, $products[0]['price']);
        $this->assertEquals(40.00, $products[1]['price']);
        $this->assertEquals(50.00, $products[2]['price']);
    }

    /**
     * Test product sorting by price descending.
     */
    public function test_can_sort_products_by_price_descending(): void
    {
        Product::factory()->create(['price' => 50.00]);
        Product::factory()->create(['price' => 30.00]);
        Product::factory()->create(['price' => 40.00]);

        $response = $this->getJson('/api/v1/products?sort=price_desc');

        $response->assertStatus(200);

        $products = $response->json('data');
        $this->assertEquals(50.00, $products[0]['price']);
        $this->assertEquals(40.00, $products[1]['price']);
        $this->assertEquals(30.00, $products[2]['price']);
    }

    /**
     * Test product sorting by newest.
     */
    public function test_can_sort_products_by_newest(): void
    {
        $oldProduct = Product::factory()->create(['created_at' => now()->subDays(2)]);
        $newProduct = Product::factory()->create(['created_at' => now()]);
        $middleProduct = Product::factory()->create(['created_at' => now()->subDay()]);

        $response = $this->getJson('/api/v1/products?sort=newest');

        $response->assertStatus(200);

        $products = $response->json('data');
        $this->assertEquals($newProduct->id, $products[0]['id']);
        $this->assertEquals($middleProduct->id, $products[1]['id']);
        $this->assertEquals($oldProduct->id, $products[2]['id']);
    }

    /**
     * Test product search functionality.
     */
    public function test_can_search_products(): void
    {
        Product::factory()->create(['title' => 'Laptop Computer', 'description' => 'A powerful laptop']);
        Product::factory()->create(['title' => 'Smartphone', 'description' => 'A modern phone']);
        Product::factory()->create(['title' => 'Computer Mouse', 'description' => 'Wireless mouse']);

        $response = $this->getJson('/api/v1/products?search=computer');

        $response->assertStatus(200);

        $products = $response->json('data');
        $this->assertCount(2, $products);
    }

    /**
     * Test pagination with custom per_page parameter.
     */
    public function test_can_paginate_with_custom_per_page(): void
    {
        // Create 25 products
        Product::factory()->count(25)->create();

        // Test with per_page=5
        $response = $this->getJson('/api/v1/products?per_page=5');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pagination' => [
                    'per_page' => 5,
                    'total' => 25,
                    'last_page' => 5,
                    'current_page' => 1,
                ],
            ]);

        $this->assertCount(5, $response->json('data'));
    }

    /**
     * Test pagination with default per_page when not specified.
     */
    public function test_pagination_uses_default_per_page_when_not_specified(): void
    {
        // Create 20 products
        Product::factory()->count(20)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pagination' => [
                    'per_page' => 15,
                    'total' => 20,
                ],
            ]);

        $this->assertCount(15, $response->json('data'));
    }

    /**
     * Test pagination enforces minimum per_page value.
     */
    public function test_pagination_enforces_minimum_per_page(): void
    {
        Product::factory()->count(10)->create();

        // Try to set per_page to 0 or negative
        $response = $this->getJson('/api/v1/products?per_page=0');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pagination' => [
                    'per_page' => 1,
                ],
            ]);
    }

    /**
     * Test pagination enforces maximum per_page value.
     */
    public function test_pagination_enforces_maximum_per_page(): void
    {
        Product::factory()->count(150)->create();

        // Try to set per_page to more than 100
        $response = $this->getJson('/api/v1/products?per_page=150');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pagination' => [
                    'per_page' => 100,
                    'current_page' => 1,
                ],
            ]);

        $this->assertCount(100, $response->json('data'));
    }

    /**
     * Test pagination works with filters and per_page.
     */
    public function test_pagination_works_with_filters_and_per_page(): void
    {
        // Create products with different categories
        Product::factory()->count(8)->create(['category' => 'electronics']);
        Product::factory()->count(5)->create(['category' => 'clothing']);

        $response = $this->getJson('/api/v1/products?category=electronics&per_page=3');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pagination' => [
                    'per_page' => 3,
                    'total' => 8,
                    'last_page' => 3,
                ],
            ]);

        $this->assertCount(3, $response->json('data'));

        // Verify all products are electronics
        foreach ($response->json('data') as $product) {
            $this->assertEquals('electronics', $product['category']);
        }
    }

    /**
     * Test pagination with per_page and page navigation.
     */
    public function test_pagination_with_page_navigation(): void
    {
        // Create 12 products
        Product::factory()->count(12)->create();

        // Get page 2 with 5 items per page
        $response = $this->getJson('/api/v1/products?per_page=5&page=2');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'pagination' => [
                    'per_page' => 5,
                    'total' => 12,
                    'last_page' => 3,
                    'current_page' => 2,
                ],
            ]);

        $this->assertCount(5, $response->json('data'));
    }
}
