<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Moneys; // Ensure this is the correct model name (capitalized)
use App\Models\Belances; // Ensure this is the correct model name (capitalized)
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\MoneyResource;
use App\Http\Resources\BelanceResource;

use Exception;

class MoneysController extends Controller
{
    /**
     * Display a listing of the accounts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $isdelete = $request->query('delete');
        $query = Moneys::orderBy('id', 'desc');

        if ($isdelete) {
            $query->where('existense',$isdelete);
        }
        $moneys = $query->get(); // یا استفاده از همه رکوردها: $query->get()
        return response()->json(MoneyResource::collection($moneys));
    }
    public function store(Request $request)
    {
        $data = $this->getData($request);
        $moneyes = Moneys::create($data);
        $ownerbelnace=Belances::create([
        'ontransaction'=>1,
        'user_id'=>$moneyes->user_id,
        'isdelete'=>0,
        'account_id'=>1,
        'type_id'=>$moneyes->id,
        'belance'=>0,
        ]);
        $moneyes->ontransaction=0;
        return response()->json([
            'money' =>new MoneyResource($moneyes),
            'belance'=> new BelanceResource($ownerbelnace),
            'message' => 'money created successfully.'
        ], 201);    }

    public function show($id)
    {
        $moneys = Moneys::with('user')->findOrFail($id);
        return response()->json($moneys);
    }

    public function update($id, Request $request)
    {
        $data = $this->getData($request);
        $moneys = Moneys::findOrFail($id);
        $moneys->update($data);
        return response()->json($moneys);
    }

    public function destroy($id)
    {
        try {
            $moneys = Moneys::findOrFail($id);
            $moneys->delete();
            return response()->json(['message' => 'Moneys was successfully deleted.'], Response::HTTP_OK);
        } catch (Exception $exception) {
            return response()->json(['error' => 'Unexpected error occurred while trying to process your request.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    protected function getData(Request $request)
    {
        $rules = [
            'ontransaction' => 'nullable',
            'existense' => 'nullable',
            'user_id' => 'nullable',
            'name' => 'nullable|string|min:1|max:255',
            'cach' => 'nullable|numeric|min:-9223372036854775800|max:9223372036854775800', 
        ];

        $data = $request->validate($rules);
        // $data['ontransaction'] = $request->has('ontransaction');
        // $data['existense'] = $request->has('existense');

        return $data;
    }
}
