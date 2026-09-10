<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly int $available,
        public readonly int $requested,
        public readonly ?string $product = null,
        string $message = 'Stock insuficiente para esta operação.'
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json(array_filter([
            'message' => $this->getMessage(),
            'product' => $this->product,
            'available' => $this->available,
            'requested' => $this->requested,
        ], fn ($value) => ! is_null($value)), 422);
    }
}