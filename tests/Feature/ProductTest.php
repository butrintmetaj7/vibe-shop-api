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
                    'products' => [
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
                ],
                'message',
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Products retrieved successfully',
            ]);

        // Verify public resource excludes internal fields
        $products = $response->json('data.products');
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
                'message' => 'Product retrieved successfully',
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

        $products = $response->json('data.products');
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

        $products = $response->json('data.products');
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

        $products = $response->json('data.products');
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

        $products = $response->json('data.products');
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

        $products = $response->json('data.products');
        $this->assertCount(2, $products);
    }
}
