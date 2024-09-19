<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => $this->type,
            'status' => $this->status,
            'service_name' => $this->service_name,
            'service_id' => $this->service_id,
            'amount' => $this->amount,
            'currency' => $this->currency?->only([
                'id',
                "code",
                'name'
            ]),
        ];
    }
}
