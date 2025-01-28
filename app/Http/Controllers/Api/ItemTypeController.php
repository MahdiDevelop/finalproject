<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Belance;
use App\Http\Controllers\Controller;

class ItemTypeController extends Controller
{
    public function index(Request $request)
    {
        return Belance::collection(Belance::all());
    }
}
