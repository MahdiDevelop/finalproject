<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'isdelete',
        'user_id',
        'cash',
        'discription',
        'amount',
        'date_created',
        'type',
        'account_id',
        'transformation',
        'bill_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Belances::class);
    }
    
}
