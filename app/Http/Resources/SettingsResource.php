<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingsResource extends JsonResource
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
        'language'=>$this->language,
        'date'=>$this->date,
        'company_pic'=>$this->company_pic,
        'compnay_name'=>$this->compnay_name,
        'description'=>$this->description,
        'address'=>$this->address,
        'phone'=>$this->phone,
        'email'=>$this->email,        
    ];}
}
