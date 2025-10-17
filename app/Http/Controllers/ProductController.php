<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use App\Services\ProductQueryService;
use Illuminate\Http\Request;

class ProductController extends Controller
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
     * Display a listing of products (public access).
     */
    public function index(Request $request)
    {
        $products = $this->queryService->getPaginated($request);

        return ApiResponse::paginated(
            $products,
            ProductResource::class
        );
    }

    /**
     * Display the specified product (public access).
     */
    public function show(Product $product)
    {
        return ApiResponse::success(
            new ProductResource($product)
        );
    }
}
