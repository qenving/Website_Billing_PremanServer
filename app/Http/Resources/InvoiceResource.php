<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'invoice_number' => $this->invoice_number,
            'client' => $this->when($request->user()->isAdmin(), [
                'id' => $this->client_id,
                'name' => $this->client?->user?->name,
                'email' => $this->client?->user?->email,
            ]),
            'items' => InvoiceItemResource::collection($this->whenLoaded('items')),
            'subtotal' => number_format($this->subtotal, 2),
            'tax' => number_format($this->tax, 2),
            'total' => number_format($this->total, 2),
            'status' => $this->status,
            'invoice_date' => $this->invoice_date?->toDateString(),
            'due_date' => $this->due_date?->toDateString(),
            'paid_date' => $this->paid_date?->toDateString(),
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
