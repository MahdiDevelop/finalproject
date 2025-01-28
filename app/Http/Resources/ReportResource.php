<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
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
        'isdelete'=>$this->isdelete,
        'user'=>$this->user_id,
        'user_name'=>$this->user->name,
        'cash'=>$this->cash,
        'discription'=>$this->discription,
        'amount'=>$this->amount,
        'date'=>$this->date_created,
        'type'=>$this->type,
        'moneyid'=>$this->account->type->id,
        'moneyType'=>$this->account->type->name,
        'account'=>$this->account_id,
        'customer'=>$this->account->account->name,
        ];
    }
}
