<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sell extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'qty',
        'weight',
        'dateInsert',
        'rate',
        'user_id',
        'isdelete',
        'purchase_price',
        'sell_price',
        'expiry_date',
        'description',
        'accounts_id',
        'bill_id',
    ];

    public function stock()
    {
        return $this->belongsTo(Item::class,'item_id');
    }
    public function bill()
    {
        return $this->belongsTo(Bill::class,'bill_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
