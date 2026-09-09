<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
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
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->input('category_id'));
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                match ($request->input('status')) {
                    'active' => $query->where('is_active', true),
                    'inactive' => $query->where('is_active', false),
                    'low_stock' => $query->lowStock(),
                    'out_of_stock' => $query->outOfStock(),
                    default => $query,
                };
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $request->user()->products()->create($request->validated());

        return (new ProductResource($product->load('category')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, string $product): ProductResource
    {
        $model = $request->user()->products()->with('category')->findOrFail($product);

        return new ProductResource($model);
    }

    public function update(UpdateProductRequest $request, string $product): ProductResource
    {
        $model = $request->user()->products()->findOrFail($product);
        $model->update($request->validated());

        return new ProductResource($model->load('category'));
    }

    public function destroy(Request $request, string $product): JsonResponse
    {
        $model = $request->user()->products()->findOrFail($product);
        $model->delete();

        return response()->json(['message' => 'Produto eliminado com sucesso.']);
    }
}