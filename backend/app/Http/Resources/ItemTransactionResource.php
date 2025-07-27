<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ItemTransactionResource extends JsonResource
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
            'type' => $this->type,
            'quantity' => $this->quantity,
            'notes' => $this->notes,
            'is_verified' => $this->is_verified,
            'metadata' => $this->metadata,
            'attachment_url' => $this->attachment ? asset('storage/' . $this->attachment) : null,
            'transaction_date' => $this->created_at->format('d-m-Y H:i:s'),

            'item' => new ItemResource($this->whenLoaded('item')),
            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
