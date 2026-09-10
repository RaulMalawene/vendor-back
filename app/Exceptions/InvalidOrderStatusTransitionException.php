<?php

namespace App\Exceptions;

use App\Enums\OrderStatus;
use Exception;
use Illuminate\Http\JsonResponse;

class InvalidOrderStatusTransitionException extends Exception
{
    public function __construct(
        public readonly OrderStatus $from,
        public readonly OrderStatus $to
    ) {
        parent::__construct("Não é possível mudar de '{$from->label()}' para '{$to->label()}'.");
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'from' => $this->from->value,
            'to' => $this->to->value,
            'allowed_next' => $this->from->next()?->value,
        ], 422);
    }
}