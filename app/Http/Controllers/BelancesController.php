<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Accounts;
use App\Models\Moneys;
use App\Models\User;
use App\Models\Belances;
// use App\Models\User;
use App\Http\Resources\BelanceResource;

use Illuminate\Http\Request;
use Exception;

class BelancesController extends Controller
{
    /**
     * Display a listing of the belances.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $search = $request->query('query');
        $moneyid = $request->query('money_id', false);
        $customerid = $request->query('CustomerId', false);
        // $belances = Belances::get();
        $belances = Belances::with('User', 'account', 'type')->get();
        if($customerid){
            $belance=Belances::where('account_id', $customerid)->get();
        return response()->json(BelanceResource::collection($belance));
        }
        if ($search && !$moneyid) {
            // $searchParts = explode(' ', $search);
            // اگر ورودی حداقل دو بخش داشته باشد
            // if (count($searchParts) >= 2) {
            //     $typeSearch = $searchParts[0];  // قسمت اول برای جستجو در نوع
            //     $accountSearch = $searchParts[1];  // قسمت دوم برای جستجو در نام حساب

            //     // فیلتر کردن رکوردها بر اساس هر دو شرط
            //     $query = Belances::with('User', 'account', 'type')
            //         ->whereHas('account', function ($q) use ($accountSearch) {
            //             $q->where('name', 'like', "%$accountSearch%");
            //         })
            //         ->whereHas('type', function ($q) use ($typeSearch) {
            //             $q->where('name', 'like', "%$typeSearch%");
            //         })
            //         ->get();
            // return response()->json($query);

            // } else {
            // اگر ورودی تنها یک بخش داشته باشد، برای جستجو در همه فیلدها انجام دهید
            $query = Belances::with('User', 'account', 'type')
                // ->whereHas('account', function ($q) use ($search) {
                //     $q->where('name', 'like', "%$search%");
                // })
                // ->orWhereHas('type', function ($q) use ($search) {
                //     $q->where('name', 'like', "%$search%");
                // })
                ->get();
            // }
            return response()->json($query);
        } elseif ($search && $moneyid) {
            $query = Belances::with('User', 'account', 'type')
                // ->whereHas('account', function ($q) use ($search) {
                //     $q->where('name', 'like', "%$search%");
                // })
                ->whereHas('type', function ($q) use ($moneyid) {
                    $q->where('id', $moneyid);
                })
                ->get();
            return response()->json($query);

        }
        return response()->json(BelanceResource::collection($belances));
    }

    /**
     * Show the form for creating a new belance (not needed in API).
     */

    /**
     * Store a new belance in the storage.
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $data = $this->getData($request);
        if (isset($data['date_created'])) {
            // Remove 'Z' and convert to a format that Carbon can parse
            $data['date_created'] = rtrim($data['date_created'], 'Z') . '+00:00';
        }
        $customer=Accounts::where('id',$data['account_id'])->first();
        if(!$customer==null){
        $customer->ontransaction=1;
        }
        $customer->save();
        $belance = Belances::create($data);
        return response()->json([
            'message' => 'Belance created successfully.',
            'belance' => new BelanceResource($belance) ,
        ], 201);
    }


    /**
     * Update the specified belance in the storage.
     *
     * @param int $id
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $data = $this->getData($request);
        if (isset($data['date_created'])) {
            // Remove 'Z' and convert to a format that Carbon can parse
            $data['date_created'] = rtrim($data['date_created'], 'Z') . '+00:00';
        }

        $belance = Belances::findOrFail($id);
        $belance->update($data);

        return response()->json([
            'message' => 'Belance updated successfully.',
        ]);
    }

    /**
     * Remove the specified belance from the storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $belance = Belances::findOrFail($id);
            $belance->delete();

            return response()->json([
                'message' => 'Belance deleted successfully.'
            ]);
        } catch (Exception $exception) {
            return response()->json([
                'error' => 'Unexpected error occurred while trying to process your request.'
            ], 500);
        }
    }

    public function show($id)
    {
        $balance = Belances::findOrFail($id); // پیدا کردن موجودیت بر اساس ID
        $balanceResource = new BelanceResource($balance); // استفاده از BelanceResource برای موجودیت

        return response()->json($balanceResource); // ارسال پاسخ JSON
    }




    /**
     * Get the request's data from the request.
     *
     * @param Illuminate\Http\Request $request
     * @return array
     */
    protected function getData(Request $request)
    {
        $rules = [
            'ontransaction' => 'boolean',
            'user_id' => '',
            'isdelete' => 'boolean',
            'account_id' => 'nullable|numeric|min:0',
            'type_id' => 'nullable|numeric|min:0',
            'belance' => '',
            'date_created' => '',
            'time' => 'nullable|date_format:j/n/Y g:i A',
        ];
        $data = $request->validate($rules);
        return $data;
    }
}
