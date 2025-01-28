<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
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
            'name'=>$this->name,
        'type_id'=>$this->type_id,
        'user'=>$this->user_id,
            'user_name'=>$this->user->name,
        'isdelete'=>$this->isdelete,
        'description'=>$this->description,
        'date_creation'=>$this->date_creation,
        'serial_number'=>$this->serial_number,
        ];
    }
}
