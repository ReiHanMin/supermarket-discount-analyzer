<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DiscountItemResource extends JsonResource
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
            'date' => $this->date,
            'supermarket' => $this->supermarket,
            'item' => $this->item,
            'original_price' => $this->original_price,
            'discounted_price' => $this->discounted_price,
            'discount_percentage' => $this->discount_percentage,
            'sold_out' => $this->sold_out,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
