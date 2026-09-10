<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orderService)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $orders = $request->user()->orders()
            ->with('customer')
            ->withCount('items')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->input('status')))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->orderService->create(
            $request->user(),
            (int) $request->validated('customer_id'),
            $request->validated('items'),
        );

        return (new OrderResource($order->load('customer', 'items')))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, string $order): OrderResource
    {
        $model = $request->user()->orders()
            ->with('customer', 'items', 'statusHistories')
            ->findOrFail($order);

        return new OrderResource($model);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, string $order): OrderResource
    {
        $model = $request->user()->orders()->findOrFail($order);

        $updated = $this->orderService->transitionTo(
            $model,
            OrderStatus::from($request->validated('status')),
            $request->user(),
            $request->validated('note'),
        );

        return new OrderResource($updated->load('customer', 'items', 'statusHistories'));
    }
}