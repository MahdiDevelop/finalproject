<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BelanceResource extends JsonResource
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
            'ontransaction'=>$this->ontransaction,
            'user_id'=>$this->user_id,
            'user_name'=>$this->user->name,
            'isdelete'=>$this->isdelete,
            'account_id'=>$this->account_id,
            'account_name'=>$this->account->name,
            'type_id'=>$this->type_id,
            'moneyId'=>$this->type->id,
            'moneyType'=>$this->type->name,
            'belance'=>$this->belance,
            'date_created'=>$this->date_created,
            'time'=>$this->time,
        ];
    }
}
