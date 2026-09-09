<?php

namespace App\Http\Controllers;

use App\Enums\StockMovementType;
use App\Http\Requests\AdjustStockRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\StockMovementResource;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InventoryController extends Controller
{
    public function __construct(private readonly StockService $stockService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $products = $request->user()->products()
            ->with('category')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->input('status') === 'low_stock', fn ($q) => $q->lowStock())
            ->when($request->input('status') === 'out_of_stock', fn ($q) => $q->outOfStock())
            ->orderBy('stock')
            ->paginate(15)
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function adjust(AdjustStockRequest $request, string $product): ProductResource
    {
        $model = $request->user()->products()->findOrFail($product);

        $updated = $this->stockService->adjust(
            $model,
            StockMovementType::from($request->validated('type')),
            (int) $request->validated('quantity'),
            $request->validated('note'),
            $request->user(),
        );

        return new ProductResource($updated->load('category'));
    }

    public function movements(Request $request, string $product): AnonymousResourceCollection
    {
        $model = $request->user()->products()->findOrFail($product);

        $movements = $model->stockMovements()->latest()->paginate(20);

        return StockMovementResource::collection($movements);
    }
}