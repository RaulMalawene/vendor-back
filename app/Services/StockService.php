<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Exceptions\InsufficientStockException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function adjust(Product $product, StockMovementType $type, int $quantity, ?string $note, User $user, ?Order $order = null): Product
    {
        return DB::transaction(function () use ($product, $type, $quantity, $note, $user, $order) {
            $locked = Product::query()
                ->whereKey($product->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $newStock = match ($type) {
                StockMovementType::In => $locked->stock + $quantity,
                StockMovementType::Out => $locked->stock - $quantity,
                StockMovementType::Adjustment => $quantity,
            };

            if ($newStock < 0) {
                throw new InsufficientStockException($locked->stock, $quantity, $locked->name);
            }

            $delta = $newStock - $locked->stock;
            $locked->update(['stock' => $newStock]);

            $locked->stockMovements()->create([
                'user_id' => $user->id,
                'order_id' => $order?->id,
                'type' => $type,
                'quantity' => $delta,
                'stock_after' => $newStock,
                'note' => $note,
            ]);

            return $locked;
        });
    }
}