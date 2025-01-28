<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moneys extends Model
{
    use HasFactory;

    protected $fillable = [
        'ontransaction',
        'existense',
        'user_id',
        'name',
        'cach',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
