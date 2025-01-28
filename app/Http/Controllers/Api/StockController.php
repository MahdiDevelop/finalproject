<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\StockResource;
use App\Models\Stock;
class StockController extends Controller
{
    public function index()
    {
        $query = Stock::query();
        // if ($request->has('delete')) {
        //     $query->where('existense', $request->input('delete'));
        // }
        return StockResource::collection(Stock::all());
    }
}