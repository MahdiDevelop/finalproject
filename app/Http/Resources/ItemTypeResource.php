<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemTypeResource extends JsonResource
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
            'picture'=>$this->picture,
            'isdelete'=>$this->isdelete,
            'user'=>$this->user_id,
            'user_name'=>$this->user->name,
            'measuring'=>$this->easuring,
        ];
    }
}
