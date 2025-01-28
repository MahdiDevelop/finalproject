<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class UserActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'path',
        'method',
        'status_code',
        'ip_address',
        'timestamp',
    ];

    protected $dates = ['timestamp'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function __toString()
    {
        return "{$this->user->username} - {$this->path} - {$this->method}";
    }
}
