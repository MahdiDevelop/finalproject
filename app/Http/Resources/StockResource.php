<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockResource extends JsonResource
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
        'accounts_id'=>$this->accounts_id,
        'stocks_id'=>$this->stocks_id,
        'qty'=>$this->qty,
        'weight'=>$this->weight,
        'dateInsert'=>$this->dateInsert,
        'rate'=>$this->rate,
        'user'=>$this->user_id,
        'user_name'=>$this->user->name,
        'isdelete'=>$this->isdelete,
        'purchase_price'=>$this->purchase_price,
        'description'=>$this->description,
        'sell_price'=>$this->sell_price
    ]; }
}
