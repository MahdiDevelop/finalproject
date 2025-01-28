<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type_id',
        'user_id',
        'isdelete',
        'description',
        'date_creation',
        'serial_number',
        'qty',
        'weight',
        'sell_price',
        'purchase_price',
        'rate',
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function type()
    {
        return $this->belongsTo(ItemType::class,'type_id');
    }
}
