<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\UserActivityLog;
use App\Http\Resources\UserActivityLogResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class testc extends Controller
{
    public function index()
    {
        return 'hide';
    }
}
