<?php

namespace App\Http\Controllers\Api;
use App\Models\Moneys;
use App\Http\Resources\MoneyResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MoneyController extends Controller
{
    public function index(Request $request)
    {
        // $query = Money::query();
        // if ($request->has('delete')) {
        //     $query->where('existense', $request->input('delete'));
        // }
        return MoneyResource::collection(Moneys::all());
    }
}