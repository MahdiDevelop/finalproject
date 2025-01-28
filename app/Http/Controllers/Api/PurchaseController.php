<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Http\Resources\PurchaseResource;
class PurchaseController extends Controller
{
    public function index()
    {
        $query = Purchase::query();
        // if ($request->has('delete')) {
        //     $query->where('existense', $request->input('delete'));
        // }
        return PurchaseResource::collection(Purchase::all());
    }

}
