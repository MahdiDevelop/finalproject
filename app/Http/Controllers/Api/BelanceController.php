<?php

namespace App\Http\Controllers\Api;

use App\Models\Belances;
use App\Http\Resources\BelanceResource;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BelanceController extends Controller
{
    public function index(Request $request)
    {
        // $query = Belance::query();

        // if ($request->has('CustomerId')) {
        //     $query->where('account', $request->input('CustomerId'))
        //           ->where('isdelete', false);
        // }

        // if ($request->has('delete')) {
        //     $query->where('isdelete', $request->input('delete'));
        // }

        // if ($request->has('startDate') && $request->has('endDate')) {
        //     $query->whereBetween('date_created', [
        //         $request->input('startDate'),
        //         $request->input('endDate')
        //     ]);
        // }

        // return BelanceResource::collection($query->orderBy('date_created', 'desc')->get());
        return 'hi';
    }

    public function pst(){
        return 'pst';
    }
    public function store(Request $request)
    {
        $balance = Belances::create($request->all());
        return response()->json([
            'message' => 'Balance created successfully!',
            'new_balance_id' => $balance->id,
        ], 201);
    }
}
