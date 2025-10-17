<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            // Fetch products from FakeStore API
            $response = Http::get('https://fakestoreapi.com/products');

            if ($response->failed()) {
                Log::error('Failed to fetch products from FakeStore API', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                $this->command->error('Failed to fetch products from FakeStore API');
                return;
            }

            $products = $response->json();
            $count = 0;

            foreach ($products as $product) {
                $rating = is_array($product['rating'] ?? null) ? $product['rating'] : [];
                Product::updateOrCreate(
                    ['external_id' => $product['id'] ?? null],
                    [
                        'title' => $product['title'] ?? 'Untitled',
                        'description' => $product['description'] ?? '',
                        'price' => isset($product['price']) && is_numeric($product['price']) ? $product['price'] : 0,
                        'category' => $product['category'] ?? 'uncategorized',
                        'image' => $product['image'] ?? null,
                        'rating_rate' => isset($rating['rate']) && is_numeric($rating['rate']) ? $rating['rate'] : null,
                        'rating_count' => isset($rating['count']) && is_numeric($rating['count']) ? (int) $rating['count'] : null,
                    ]
                );
                $count++;
            }

            $this->command->info("Successfully seeded {$count} products from FakeStore API");

        } catch (\Exception $e) {
            Log::error('Error seeding products', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->command->error('Error seeding products: ' . $e->getMessage());
        }
    }
}
