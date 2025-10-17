<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\AdminProductResource;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use App\Services\ProductQueryService;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    /**
     * @var ProductQueryService
     */
    protected $queryService;

    /**
     * Create a new controller instance.
     */
    public function __construct(ProductQueryService $queryService)
    {
        $this->queryService = $queryService;
    }

    /**
     * Display a listing of products (admin access).
     */
    public function index(Request $request)
    {
        $query = $this->queryService->buildQuery($request);
        $products = $query->paginate();

        return ApiResponse::paginated(
            $products,
            AdminProductResource::class
        );
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated());

        return ApiResponse::success(
            new AdminProductResource($product),
            'Product created successfully',
            201
        );
    }

    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        return ApiResponse::success(
            new AdminProductResource($product)
        );
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return ApiResponse::success(
            new AdminProductResource($product->fresh()),
            'Product updated successfully'
        );
    }

    /**
     * Remove the specified product.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return ApiResponse::success(
            null,
            'Product deleted successfully'
        );
    }
}
