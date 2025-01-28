<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'stocks_id',
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
        'bill_id',
    ];

    public function stock()
    {
        return $this->belongsTo(item::class, 'stocks_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function bill()
    {
        return $this->belongsTo(Bill::class,'bill_id');
    }
}