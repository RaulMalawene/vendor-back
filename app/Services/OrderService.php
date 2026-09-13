<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\StockMovementType;
use App\Exceptions\InvalidOrderStatusTransitionException;
use App\Models\Order;
use App\Models\User;
use App\Notifications\NewOrderReceived;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(private readonly StockService $stockService) {}

    public function create(User $user, int $customerId, array $items): Order
    {
        return DB::transaction(function () use ($user, $customerId, $items) {
            $productIds = array_column($items, 'product_id');
            $products = $user->products()->whereIn('id', $productIds)->get()->keyBy('id');

            $order = $user->orders()->create([
                'customer_id' => $customerId,
                'number' => (string) Str::uuid(),
                'status' => OrderStatus::Pending,
                'total' => 0,
                'placed_at' => now(),
            ]);

            $order->update([
                'number' => 'ORD-'.now()->year.'-'.str_pad((string) $order->id, 5, '0', STR_PAD_LEFT),
            ]);

            $total = 0;

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $product->price;
                $subtotal = $unitPrice * $quantity;
                $total += $subtotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'subtotal' => $subtotal,
                ]);
            }

            $order->update(['total' => $total]);

            $order->statusHistories()->create([
                'user_id' => $user->id,
                'from_status' => null,
                'to_status' => OrderStatus::Pending,
                'note' => 'Encomenda criada.',
            ]);

            $user->notify(new NewOrderReceived($order));

            return $order;
        });
    }

    public function transitionTo(Order $order, OrderStatus $target, User $user, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $target, $user, $note) {
            $current = $order->status;

            if (! $current->canTransitionTo($target)) {
                throw new InvalidOrderStatusTransitionException($current, $target);
            }

            if ($target === OrderStatus::Confirmed) {
                $this->consumeStock($order, $user);
            }

            $order->update(['status' => $target]);

            $order->statusHistories()->create([
                'user_id' => $user->id,
                'from_status' => $current,
                'to_status' => $target,
                'note' => $note,
            ]);

            return $order->fresh();
        });
    }

    private function consumeStock(Order $order, User $user): void
    {
        $items = $order->items()->orderBy('product_id')->get();

        foreach ($items as $item) {
            $product = $user->products()->whereKey($item->product_id)->firstOrFail();

            $this->stockService->adjust(
                $product,
                StockMovementType::Out,
                $item->quantity,
                'Confirmação da encomenda '.$order->number,
                $user,
                $order,
            );
        }
    }
}
