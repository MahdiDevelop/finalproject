<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MoneyResource extends JsonResource
{ public function toArray(Request $request): array
    {
        return [
        "id"=> $this->id,
        'ontransaction'=>$this->ontransaction,
        'existense'=>$this->existense,
        'user'=>$this->user_id,
            'user_name'=>$this->user->name,
        'name'=>$this->name,
        'cach'=>$this->cach,
        ];
    }
}