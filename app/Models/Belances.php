<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Belances extends Model
{
    use HasFactory;

    protected $fillable = [
        'ontransaction',
        'user_id',
        'isdelete',
        'account_id',
        'type_id',
        'belance',
        'date_created',
        'time',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Accounts::class);
    }

    public function type()
    {
        return $this->belongsTo(Moneys::class);
    }
}
