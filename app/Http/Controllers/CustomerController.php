<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return CustomerResource::collection(
            $request->user()->customers()->orderBy('name')->get()
        );
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $request->user()->customers()->create($request->validated());

        return (new CustomerResource($customer))->response()->setStatusCode(201);
    }
}