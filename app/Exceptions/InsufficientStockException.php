<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InsufficientStockException extends Exception
{
    public function __construct(
        public readonly int $available,
        public readonly int $requested,
        string $message = 'Stock insuficiente para esta operação.'
    ) {
        parent::__construct($message);
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'available' => $this->available,
            'requested' => $this->requested,
        ], 422);
    }
}