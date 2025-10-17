<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array for public users.
     * Excludes internal fields like external_id, created_at, and updated_at.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => $this->price,
            'category' => $this->category,
            'image' => $this->image,
            'rating' => [
                'rate' => $this->rating_rate,
                'count' => $this->rating_count,
            ],
        ];
    }
}
