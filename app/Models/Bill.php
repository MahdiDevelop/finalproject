<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'dateInsert',
        'total',
        'PaidAmount',
        'Remain',
        'temp_customer',
        'accounts_id',
        'money_id',
        'user_id',
        'type',
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function accounts()
    {
        return $this->belongsTo(Belances::class,'accounts_id');
    }
    public function money()
    {
        return $this->belongsTo(Moneys::class,'money_id');
    }
}