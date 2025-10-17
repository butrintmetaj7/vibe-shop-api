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
                Product::updateOrCreate(
                    ['external_id' => $product['id']],
                    [
                        'title' => $product['title'],
                        'description' => $product['description'],
                        'price' => $product['price'],
                        'category' => $product['category'],
                        'image' => $product['image'] ?? null,
                        'rating_rate' => $product['rating']['rate'] ?? null,
                        'rating_count' => $product['rating']['count'] ?? null,
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
