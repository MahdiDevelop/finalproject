<?php

namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use App\Models\Belances;
use App\Http\Controllers\Controller;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        return Belances::collection(Belances::all());
    }
}
