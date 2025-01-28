<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    use HasFactory;

    protected $fillable = [
        'language',
        'date',
        'company_pic',
        'company_name',
        'description',
        'address',
        'phone',
        'email',
    ];
}
