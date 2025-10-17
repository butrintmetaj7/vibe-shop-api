<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductQueryService
{
    /**
     * Build a product query with filters and sorting applied.
     *
     * @param Request $request
     * @return Builder
     */
    public function buildQuery(Request $request): Builder
    {
        $query = Product::query();

        // Filter by category if provided
        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        // Filter by search query if provided
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $this->applySorting($query, $request->input('sort', 'id_asc'));

        return $query;
    }

    /**
     * Get paginated products with filters applied.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginated(Request $request)
    {
        $query = $this->buildQuery($request);
        $perPage = max(1, min($request->integer('per_page', 15), 100));

        return $query->paginate($perPage);
    }

    /**
     * Apply sorting to the query.
     *
     * @param Builder $query
     * @param string $sort
     * @return void
     */
    protected function applySorting(Builder $query, string $sort): void
    {
        match ($sort) {
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            'newest' => $query->orderBy('created_at', 'desc'),
            default => $query->orderBy('id', 'asc'),
        };
    }
}

