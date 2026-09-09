<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'contact_name' => $this->contact_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'bank_name' => $this->bank_name,
            'bank_account_holder' => $this->bank_account_holder,
            'bank_account_number_masked' => $this->maskAccountNumber(),
            'bank_account_number' => $this->when(
                $request->boolean('reveal'),
                $this->bank_account_number
            ),
            'updated_at' => $this->updated_at,
        ];
    }

    private function maskAccountNumber(): ?string
    {
        $number = $this->bank_account_number;

        if (! $number) {
            return null;
        }

        $visible = substr($number, -4);

        return str_repeat('•', max(strlen($number) - 4, 0)) . $visible;
    }
}