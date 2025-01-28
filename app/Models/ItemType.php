<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'picture',
        'isdelete',
        'user_id',
        'measuring',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
