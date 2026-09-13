<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sku' => $this->sku,
            'price' => $this->price,
            'stock' => $this->stock,
            'stock_status' => $this->stockStatus(),
            'category' => new CategoryResource($this->whenLoaded('category')),
        ];
    }
}
