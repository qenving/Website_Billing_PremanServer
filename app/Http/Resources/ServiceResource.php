<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product' => [
                'id' => $this->product_id,
                'name' => $this->product->name,
            ],
            'domain' => $this->domain,
            'username' => $this->username,
            'status' => $this->status,
            'billing_cycle' => $this->billing_cycle,
            'price' => number_format($this->price, 2),
            'registration_date' => $this->registration_date?->toDateTimeString(),
            'next_due_date' => $this->next_due_date?->toDateTimeString(),
            'termination_date' => $this->termination_date?->toDateTimeString(),
            'dedicated_ip' => $this->dedicated_ip,
            'server' => $this->when($request->user()->isAdmin(), [
                'id' => $this->server_id,
                'name' => $this->server?->name,
            ]),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
