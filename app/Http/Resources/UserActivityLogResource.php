<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserActivityLogResource extends JsonResource
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
            'user'=>$this->user_id,
            'user_name'=>$this->user->name,
            'path'=>$this->path,
            'method'=>$this->method,
            'status_code'=>$this->status_code,
            'ip_address'=>$this->ip_address,
            'timestamp'=>$this->timestamp,
        ];
    }
}
