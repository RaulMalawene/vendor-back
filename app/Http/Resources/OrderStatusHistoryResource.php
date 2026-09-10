<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'from' => $this->from_status?->value,
            'from_label' => $this->from_status?->label(),
            'to' => $this->to_status->value,
            'to_label' => $this->to_status->label(),
            'note' => $this->note,
            'created_at' => $this->created_at,
        ];
    }
}