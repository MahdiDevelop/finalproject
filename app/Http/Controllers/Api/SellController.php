<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sell;
use App\Http\Resources\SellResource;
class SellController extends Controller
{
    public function index()
    {
        $query = Sell::query();
        // if ($request->has('delete')) {
        //     $query->where('existense', $request->input('delete'));
        // }
        return SellResource::collection(Sell::all());
    }
}
