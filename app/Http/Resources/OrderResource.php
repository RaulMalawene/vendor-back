<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'status' => $this->status->value,
            'status_label' => $this->status->label(),
            'next_status' => $this->status->next()?->value,
            'next_status_label' => $this->status->next()?->label(),
            'total' => $this->total,
            'placed_at' => $this->placed_at,
            'items_count' => $this->whenCounted('items'),
            'customer' => new CustomerResource($this->whenLoaded('customer')),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'status_history' => OrderStatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'created_at' => $this->created_at,
        ];
    }
}