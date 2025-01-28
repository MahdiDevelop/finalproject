<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'=>$this->id,
            'item_id'=>$this->item_id,
            'qty'=>$this->qty,
            'weight'=>$this->weight,
            'dateInsert'=>$this->dateInsert,
            'rate'=>$this->rate,
            'user'=>$this->user_id,
            'user_name'=>$this->user->name,
            'isdelete'=>$this->isdelete,
            'purchase_price'=>$this->purchase_price,
            'sell_price'=>$this->sell_price,
            'expiry_date'=>$this->expiry_date,
            'description'=>$this->description,
        ];
    }
}
