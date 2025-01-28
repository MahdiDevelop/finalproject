<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountsResource extends JsonResource

{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'ontransaction'=>$this->ontransaction,
            'isdelete'=>$this->isdelete,
            'user'=>$this->user,
            'user_name'=>$this->user->name,
            'name'=>$this->name,
            'father_name'=>$this->father_name,
            'national_id_number'=>$this->national_id_number,
            'phone_number'=>$this->phone_number,
            'whatsup_number'=>$this->whatsup_number,
            'address'=>$this->address,
            'profile_picture'=>$this->profile_picture,
            'national_id_picture'=>$this->national_id_picture,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'date_created'=>$this->date_created,
        ];
    }
}
