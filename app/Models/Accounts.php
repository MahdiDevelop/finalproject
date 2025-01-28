<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accounts extends Model
{
    use HasFactory;

    protected $fillable = [
        'ontransaction',
        'isdelete',
        'user_id',
        'name',
        'father_name',
        'national_id_number',
        'phone_number',
        'whatsup_number',
        'addresss',
        'profile_picture',
        'national_id_picture',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
