<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class UserQueryService
{
    public function buildQuery(Request $request): Builder
    {
        $query = User::query();

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $this->applySorting($query, $request->input('sort', 'id_asc'));

        return $query;
    }

    public function getPaginated(Request $request)
    {
        $query = $this->buildQuery($request);
        $perPage = max(1, min($request->integer('per_page', 15), 100));

        return $query->paginate($perPage);
    }

    protected function applySorting(Builder $query, string $sort): void
    {
        match ($sort) {
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            default => $query->orderBy('id', 'asc'),
        };
    }
}

