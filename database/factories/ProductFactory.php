<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['electronics', 'clothing', 'jewelery', "men's clothing", "women's clothing"];

        return [
            'external_id' => fake()->unique()->numberBetween(1, 10000),
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 5, 1000),
            'category' => fake()->randomElement($categories),
            'image' => fake()->imageUrl(640, 480, 'products'),
            'rating_rate' => fake()->randomFloat(2, 0, 5),
            'rating_count' => fake()->numberBetween(0, 500),
        ];
    }
}
