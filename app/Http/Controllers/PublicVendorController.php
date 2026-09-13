<?php

namespace App\Http\Controllers;

use App\Http\Resources\PublicProductResource;
use App\Http\Resources\PublicVendorResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PublicVendorController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $vendors = User::query()
            ->whereHas('company')
            ->with('company')
            ->withCount(['products' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return PublicVendorResource::collection($vendors);
    }

    public function products(Request $request, string $vendor): AnonymousResourceCollection
    {
        $user = User::whereHas('company')->findOrFail($vendor);

        $products = $user->products()
            ->with('category')
            ->where('is_active', true)
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return PublicProductResource::collection($products);
    }
}
