<?php

namespace Tests\Unit;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that product price is cast to decimal.
     */
    public function test_price_is_cast_to_decimal(): void
    {
        $product = Product::factory()->create(['price' => 99.99]);

        $this->assertIsString($product->price);
        $this->assertEquals('99.99', $product->price);
    }

    /**
     * Test that rating_rate is cast to decimal.
     */
    public function test_rating_rate_is_cast_to_decimal(): void
    {
        $product = Product::factory()->create(['rating_rate' => 4.5]);

        $this->assertIsString($product->rating_rate);
        $this->assertEquals('4.50', $product->rating_rate);
    }

    /**
     * Test that rating_count is cast to integer.
     */
    public function test_rating_count_is_cast_to_integer(): void
    {
        $product = Product::factory()->create(['rating_count' => 100]);

        $this->assertIsInt($product->rating_count);
        $this->assertEquals(100, $product->rating_count);
    }

    /**
     * Test that external_id is cast to integer.
     */
    public function test_external_id_is_cast_to_integer(): void
    {
        $product = Product::factory()->create(['external_id' => 123]);

        $this->assertIsInt($product->external_id);
        $this->assertEquals(123, $product->external_id);
    }

    /**
     * Test byCategory scope.
     */
    public function test_by_category_scope_filters_products(): void
    {
        Product::factory()->create(['category' => 'electronics']);
        Product::factory()->create(['category' => 'clothing']);
        Product::factory()->create(['category' => 'electronics']);

        $electronicsProducts = Product::byCategory('electronics')->get();

        $this->assertCount(2, $electronicsProducts);
        foreach ($electronicsProducts as $product) {
            $this->assertEquals('electronics', $product->category);
        }
    }

    /**
     * Test formatted price attribute accessor.
     */
    public function test_formatted_price_attribute(): void
    {
        $product = Product::factory()->create(['price' => 99.99]);

        $this->assertEquals('$99.99', $product->formatted_price);
    }

    /**
     * Test formatted price with different values.
     */
    public function test_formatted_price_formats_correctly(): void
    {
        $product1 = Product::factory()->create(['price' => 10]);
        $this->assertEquals('$10.00', $product1->formatted_price);

        $product2 = Product::factory()->create(['price' => 1234.56]);
        $this->assertEquals('$1,234.56', $product2->formatted_price);
    }

    /**
     * Test that fillable attributes can be mass assigned.
     */
    public function test_fillable_attributes(): void
    {
        $productData = [
            'external_id' => 123,
            'title' => 'Test Product',
            'description' => 'Test Description',
            'price' => 99.99,
            'category' => 'electronics',
            'image' => 'https://example.com/image.jpg',
            'rating_rate' => 4.5,
            'rating_count' => 100,
        ];

        $product = Product::create($productData);

        $this->assertEquals(123, $product->external_id);
        $this->assertEquals('Test Product', $product->title);
        $this->assertEquals('Test Description', $product->description);
        $this->assertEquals('99.99', $product->price);
        $this->assertEquals('electronics', $product->category);
        $this->assertEquals('https://example.com/image.jpg', $product->image);
        $this->assertEquals('4.50', $product->rating_rate);
        $this->assertEquals(100, $product->rating_count);
    }
}
