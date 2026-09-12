<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
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

    public function show(Request $request, string $customer): CustomerResource
    {
        $model = $request->user()->customers()->findOrFail($customer);

        return new CustomerResource($model);
    }

    public function update(UpdateCustomerRequest $request, string $customer): CustomerResource
    {
        $model = $request->user()->customers()->findOrFail($customer);
        $model->update($request->validated());

        return new CustomerResource($model);
    }

    public function destroy(Request $request, string $customer): JsonResponse
    {
        $model = $request->user()->customers()->findOrFail($customer);

        if ($model->orders()->exists()) {
            return response()->json([
                'message' => 'Não é possível eliminar um cliente com encomendas associadas.',
            ], 409);
        }

        $model->delete();

        return response()->json(['message' => 'Cliente eliminado com sucesso.']);
    }
}
