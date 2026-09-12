<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $categories = $request->user()->categories()
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $request->user()->categories()->create($request->validated());

        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function show(Request $request, string $category): CategoryResource
    {
        $model = $request->user()->categories()->withCount('products')->findOrFail($category);

        return new CategoryResource($model);
    }

    public function update(UpdateCategoryRequest $request, string $category): CategoryResource
    {
        $model = $request->user()->categories()->findOrFail($category);
        $model->update($request->validated());

        return new CategoryResource($model);
    }

    public function destroy(Request $request, string $category): JsonResponse
    {
        $model = $request->user()->categories()->findOrFail($category);
        $model->delete();

        return response()->json(['message' => 'Categoria eliminada com sucesso.']);
    }
}