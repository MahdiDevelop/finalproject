<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\BelanceResource;
use App\Http\Resources\MoneyResource;
use App\Models\Item;
use App\Models\User;
use App\Models\Sell;
use App\Models\Moneys;
use App\Models\Belances;
use App\Models\Report;
use App\Models\Accounts;
use App\Models\Bill;
use Illuminate\Http\Request;
use Exception;

class SellsController extends Controller
{
    // لیست فروش‌ها
    public function index(Request $request)
{
    $isDelete = intval($request->query('delete', 0)); // Default to 0 if not provided
    $perPage = intval($request->query('perPage', 10)); // Default 10 items per page
    $currentPage = max(1, intval($request->query('page', 1))); // Default to page 1

    // Fetch and group data directly from the database
    $query = Sell::with(['bill.money', 'user', 'bill.accounts.account', 'stock'])
        ->where('isdelete', $isDelete)
        ->orderBy('dateInsert', 'desc');

    // Fetch grouped data
    $groupedData = $query->get()->groupBy('bill.id');

    // Map the grouped data
    $data = $groupedData->map(function ($group) {
        $firstSell = $group->first();
        $bill = $firstSell->bill;

        return [
            'bill' => $bill,
            'money' => $bill->money,
            'sells' => $group->toArray(),
        ];
    });

    // Paginate manually
    $totalGroups = $data->count();
    $paginatedData = $data
        ->slice(($currentPage - 1) * $perPage, $perPage)
        ->values(); // Reset array keys

    // Return response with pagination
    return response()->json([
        'data' => $paginatedData,
        'total' => $totalGroups,
        'current_page' => $currentPage,
        'per_page' => $perPage,
        'last_page' => ceil($totalGroups / $perPage),
    ]);
}


    // ذخیره فروش جدید
    public function store(Request $request)
    {
        $Exesting = $request->query('Exesting');
        $money = $request->query('money');
        $accounts_id = $request->query('accounts_id');
        $PaidAmount = $request->query('PaidAmount');
        $TotalAmount = $request->query('TotalAmount');
        $CustomerName = $request->query('CustomerName');
        $create = null;
        $validatedData = $request->validate([
            'arr' => 'required|array',
            'arr.*.item_id' => '',
            'arr.*.bill_id' => '',
            'arr.*.accounts_id' => '',
            'arr.*.qty' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'arr.*.weight' => 'nullable',
            'arr.*.dateInsert' => '',
            'arr.*.rate' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'arr.*.user_id' => 'required',
            'arr.*.isdelete' => 'nullable|boolean',
            'arr.*.purchase_price' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'arr.*.sell_price' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'arr.*.expiry_date' => '',
            'arr.*.description' => 'nullable',
        ]);
        $moneys = [];
        if ($Exesting === 'ok') {
            // if($accounts_id==='no'){
            //     $customer=Accounts::create([
            //         'name'=>$CustomerName,
            //         'user_id'=>$validatedData['arr'][0]['user_id'],
            //     ]);
            //     $accounts_id=$customer->id;
            // }
            $create = Belances::where('type_id', $money)->where('account_id', $accounts_id)->first();
            if ($create) {
                $create->belance -= $TotalAmount - $PaidAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
                $create->save();
                $date = rtrim($validatedData['arr'][0]['dateInsert'], 'Z') . '+00:00';
                $product = Belances::where('account_id', 1)
                    ->where('type_id', $money)
                    ->first();
                $product->belance += $TotalAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
                $product->save();
                $bill = Bill::create([
                    'accounts_id' => $create->id,
                    'user_id' => $validatedData['arr'][0]['user_id'],
                    'total' => $TotalAmount,
                    'PaidAmount' => $PaidAmount,
                    'Remain' => $PaidAmount - $TotalAmount,
                    'dateInsert' => $date,
                    'type' => 'sell',
                    'money_id' => $money,
                ]);
                $reportinbill = Report::create([
                    'isdelete' => 0,
                    'user_id' => $validatedData['arr'][0]['user_id'],
                    'cash',
                    'discription' => 'By Bill Number of ' . strval($bill->id),
                    // 'discription'=>'By Bill Number of '+strval($bill->id),
                    'amount' => $TotalAmount - $PaidAmount,
                    'date_created' => $date,
                    'type' => 'withdraw',
                    'account_id' => $create->id,
                    // 'transformation',
                ]);
                $money = Moneys::where('id', $money)->first();
                $money->cach += $PaidAmount;
                $money->save();
                // return response()->json([
                //     'message' => 'Report created successfully',
                //     // 'report' => $report->id,
                // ], 201);
                // $$$$$$$$$$
                foreach ($validatedData['arr'] as $data) {
                    // $data = $this->getData($row);
                    $this->AddItem($data);
                    $data['bill_id'] = $bill->id;
                    // $TotalAmount+=(int)$data['purchase_price']*(int)$data['qty'];
                    try {
                        if (isset($data['dateInsert'])) {
                            // Remove 'Z' and convert to a format that Carbon can parse
                            $data['dateInsert'] = rtrim($data['dateInsert'], 'Z') . '+00:00';
                        }
                        if (isset($data['expiry_date'])) {
                            // Remove 'Z' and convert to a format that Carbon can parse
                            $data['expiry_date'] = rtrim($data['expiry_date'], 'Z') . '+00:00';
                        }
                        $report = Sell::create($data);
                        // return response()->json([
                        //     'message' => 'Report created successfully',
                        //     'bill' => $bill,
                        //     'belance'=>new BelanceResource($create),
                        //     'mainbelance'=>new BelanceResource($product),
                        //     'moneys'=>new MoneyResource($money)
                        // ], 201);
                    } catch (Exception $e) {
                        return response()->json([
                            'error' => 'Failed to create report'
                        ], 500);
                    }
                }
                return response()->json([
                    'message' => 'Report created successfully',
                    'bill' => $bill,
                    'belance' => new BelanceResource($create),
                    'mainbelance' => new BelanceResource($product),
                    'moneys' => new MoneyResource($money)
                ], 201);
            } else {
                $create = Belances::create([
                    'type_id' => $money,
                    'user_id' => $validatedData['arr'][0]['user_id'],
                    'belance' => $PaidAmount - $TotalAmount,
                    'account_id' => $accounts_id,
                ]);
                $product = Belances::where('account_id', 1)
                    ->where('type_id', $money)
                    ->first();
                $product->belance += $TotalAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
                $product->save();
                $date = rtrim($validatedData['arr'][0]['dateInsert'], 'Z') . '+00:00';
                $bill = Bill::create([
                    'accounts_id' => $create->id,
                    'user_id' => $validatedData['arr'][0]['user_id'],
                    'total' => $TotalAmount,
                    'PaidAmount' => $PaidAmount,
                    'Remain' => $PaidAmount - $TotalAmount,
                    'dateInsert' => $date,
                    'type' => 'sell',
                    'money_id' => $money,
                ]);
                $reportinbill = Report::create([
                    'isdelete' => 0,
                    'user_id' => $validatedData['arr'][0]['user_id'],
                    'cash',
                    'discription' => 'By Bill Number of ' . strval($bill->id),

                    // 'discription'=>'By Bill Number of '+strval($bill->id),
                    'amount' => $TotalAmount - $PaidAmount,
                    'date_created' => $date,
                    'type' => 'withdraw',
                    'account_id' => $create->id,
                ]);
                $money = Moneys::where('id', $money)->first();
                $money->cach += $PaidAmount;
                $money->save();
                foreach ($validatedData['arr'] as $data) {
                    $this->AddItem($data);
                    // $data = $this->getData($row);
                    $data['bill_id'] = $bill->id;
                    // $TotalAmount+=(int)$data['purchase_price']*(int)$data['qty'];
                    try {
                        if (isset($data['dateInsert'])) {
                            // Remove 'Z' and convert to a format that Carbon can parse
                            $data['dateInsert'] = rtrim($data['dateInsert'], 'Z') . '+00:00';
                        }
                        if (isset($data['expiry_date'])) {
                            // Remove 'Z' and convert to a format that Carbon can parse
                            $data['expiry_date'] = rtrim($data['expiry_date'], 'Z') . '+00:00';
                        }
                        $report = Sell::create($data);
                        // return response()->json([
                        //     'message' => 'Report created successfully',
                        //     'bill' => $bill,
                        //     'belance'=>new BelanceResource($create),
                        //     'mainbelance'=>new BelanceResource($product),
                        //     'moneys'=>new MoneyResource($money)
                        // ], 201);
                    } catch (Exception $e) {
                        return response()->json([
                            'error' => 'Failed to create report'
                        ], 500);
                    }
                }
            }
            return response()->json([
                'message' => 'Report created successfully',
                'bill' => $bill,
                'belance' => new BelanceResource($create),
                'mainbelance' => new BelanceResource($product),
                'moneys' => new MoneyResource($money)
            ], 201);
        } else {
            $product = Belances::where('account_id', 1)
                ->where('type_id', $money)
                ->first();
            $product->belance += $TotalAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
            $product->save();
            $date = rtrim($validatedData['arr'][0]['dateInsert'], 'Z') . '+00:00';
            $bill = Bill::create([                'user_id' => $validatedData['arr'][0]['user_id'],
                'total' => $TotalAmount,
                'PaidAmount' => $TotalAmount,
                'Remain' => $TotalAmount,
                'dateInsert' => $date,
                'type' => 'sell',
                'temp_customer' => $CustomerName,
                'money_id' => $money,
            ]);
            $money = Moneys::where('id', $money)->first();
            $money->cach += $TotalAmount;
            $money->save();

            foreach ($validatedData['arr'] as $data) {
                // $data = $this->getData($row);
                $data['bill_id'] = $bill->id;
                $this->AddItem($data);
                try {
                    if (isset($data['dateInsert'])) {
                        // Remove 'Z' and convert to a format that Carbon can parse
                        $data['dateInsert'] = rtrim($data['dateInsert'], 'Z') . '+00:00';
                    }
                    if (isset($data['expiry_date'])) {
                        // Remove 'Z' and convert to a format that Carbon can parse
                        $data['expiry_date'] = rtrim($data['expiry_date'], 'Z') . '+00:00';
                    }
                    $report = Sell::create($data);
                    return response()->json([
                        'message' => 'Report created successfully',
                        'bill' => $bill,
                        // 'belance'=>new BelanceResource($create),
                        'mainbelance' => new BelanceResource($product),
                        'moneys' => new MoneyResource($money)
                    ], 201);
                } catch (Exception $e) {
                    return response()->json([
                        'error' => $e
                    ], 500);
                }

            }
            return response()->json([
                'message' => 'Report created successfully',
                'bill' => $bill,
                // 'belance'=>new BelanceResource($create),
                'mainbelance' => new BelanceResource($product),
                'moneys' => new MoneyResource($money)
            ], 201);
        }
        // return response()->json([
        //     'message' => 'Report created successfully',
        //     'bill' => $bill,
        //     'belance'=>new BelanceResource($create),
        //     'mainbelance'=>new BelanceResource($product),
        //     'moneys'=>new MoneyResource($moneys)
        // ], 201);











        // $account = $request->query('account');
        // $data = $this->getData($request);

        // if (isset($data['item_id']) && isset($data['qty'])) {
        // $product = Item::where('id', $data['item_id'])->first();
        // $product->qty -= $data['qty']; // Assuming the quantity field in the stock table
        // $product->sell_price = $data['sell_price']; // Assuming the quantity field in the stock table
        // $product->save();
        // }
        // if($account=='ok'){
        //     if (isset($data['sell_price'])) {
        //         $product = Belances::where('account_id', 1)
        //                ->where('type_id', $data['money'])
        //                ->first();
        //             $product->belance +=($data['sell_price'])*($data['qty']); // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
        //             $product->save();
        //             $money=Moneys::where('id',$data['money'])->first();
        //             $money->cach+=($data['sell_price'])*($data['qty']);
        //             $money->save();
        //         }
        // }
        //         try {
        //     if (isset($data['dateInsert'])) {
        //         $data['dateInsert'] = rtrim($data['dateInsert'], 'Z') . '+00:00';
        //     }
        //     if (isset($data['expiry_date'])) {
        //         $data['expiry_date'] = rtrim($data['expiry_date'], 'Z') . '+00:00';
        //     }
        //     $report = Sell::create($data);
        //     return response()->json([
        //         'message' => 'Report created successfully',
        //         'report' => $report->id,
        //     ], 201);
        // } catch (Exception $e) {
        //     return response()->json([
        //         'error' => $e
        //     ], 500);
        // }
    }

    protected function AddItem($data)
    {
        $product = Item::where('id', $data['item_id'])->first();
        $product->qty -= $data['qty']; // Assuming the quantity field in the stock table
        $product->sell_price = $data['sell_price']; // Assuming the quantity field in the stock table
        $product->save();
    }
    protected function DeleteItem($data)
    {
        $product = Item::where('id', $data['item_id'])->first();
        $product->qty += (int)$data['qty']; // Assuming the quantity field in the stock table
        // $product->sell_price = $data['sell_price']; // Assuming the quantity field in the stock table
        $product->save();
    }
    // نمایش جزئیات یک فروش
    public function show($id)
    {
        $sell = Sell::with('stock', 'user')->findOrFail($id);
        return response()->json($sell);
    }

    // ویرایش فروش
    public function update($id, Request $request)
    {
        $prevMoneyId = $request->query('prevMoney');
        $Exesting = $request->query('Exesting');
        $money = $request->query('money');
        $Accounts_id = $request->query('Accounts_id');
        $accounts_id = $request->query('accounts_id');
        $PaidAmount = $request->query('PaidAmount');
        $TotalAmount = $request->query('TotalAmount');
        $Bill_id = $request->query('Bill_id');
        $primaryPaidAmount = $request->query('primaryPaidAmount');
        $primaryTotalAmount = $request->query('primaryTotalAmount');
        $CustomerName = $request->query('CustomerName');
        // $haveAccount=$request->query('haveAccount');
        // $$validatedData = $request->validate([
        //     'arr' => 'required|array',
        //     'arr.*.stocks_id' => 'required|integer',
        //     'arr.*.qty' => 'required|numeric',
        //     'arr.*.weight' => 'required|numeric',
        //     'arr.*.price' => 'required|numeric',
        //     'Exesting' => 'required|in:ok,no',
        //     'money' => 'required|integer',
        //     'accounts_id' => 'required|integer',
        //     'arr.*.dateInsert' => 'nullable|date',
        //     'arr.*.expiry_date' => 'nullable|date',
        // ]);
        $create = null;
        $validatedData = $request->validate([
            'arr' => 'required|array',
            'arr.*.item_id' => '',
            'arr.*.bill_id' => '',
            'arr.*.accounts_id' => '',
            'arr.*.qty' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'arr.*.weight' => 'nullable',
            'arr.*.dateInsert' => '',
            'arr.*.rate' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'arr.*.user_id' => 'required',
            'arr.*.isdelete' => 'nullable|boolean',
            'arr.*.purchase_price' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'arr.*.sell_price' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'arr.*.expiry_date' => '',
            'arr.*.description' => 'nullable',
            // 'arr.*.tepm_customer' => 'nullable',
        ]);
        // $customer=[];
        // $moneys = [];
        $secondcreate = Belances::where('id', $Accounts_id)->first();
        if ($secondcreate) {
            $secondcreate->belance += $primaryTotalAmount - $primaryPaidAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
            $secondcreate->save();
        }
        if ($Exesting === 'ok') {
            $create = Belances::where('type_id', $money)->where('account_id', $accounts_id)->first();
            if ($create) {
                if($Accounts_id){
                    $prveBelance=Belances::where('id',$Accounts_id)->first();
                    $prevMainBelance = Belances::where('account_id', 1)
                    ->where('type_id', $prveBelance->type_id)
                    ->first();
                    $prevMainBelance->belance-=$primaryTotalAmount;
                    $prevMainBelance->save();
                    $prveBelance->belance+=$primaryTotalAmount - $primaryPaidAmount ;
                    $prveBelance->save();
                    $prveMoney=Moneys::where('id',$money)->first();
                    $prveMoney->cach-=$primaryTotalAmount - $primaryPaidAmount;
                    $prveMoney->save();
                    $reportinbill = Report::where('bill_id', $Bill_id)->first();
                    if ($reportinbill) {
                        $reportinbill->delete();
                    }
                }
                else{
                    $prevMainBelance = Belances::where('account_id', 1)
                    ->where('type_id', $prevMoneyId)
                    ->first();
                    $prevMainBelance->belance-=$primaryTotalAmount;
                    $prevMainBelance->save();
                    $prveMoney=Moneys::where('id',$money)->first();
                    $prveMoney->cach-=$primaryTotalAmount;
                    $prveMoney->save();
                }
                $create->belance -= $TotalAmount - $PaidAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
                $create->save();
                $date = rtrim($validatedData['arr'][0]['dateInsert'], 'Z') . '+00:00';
                $product = Belances::where('account_id', 1)
                    ->where('type_id', $money)
                    ->first();
                // $product->belance -= $primaryTotalAmount;
                $product->belance += $TotalAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
                $product->save();
                $bill = Bill::where('id', $Bill_id)->first();
                $bill->accounts_id = $create->id;
                $bill->user_id = $validatedData['arr'][0]['user_id'];
                // $bill_total = $TotalAmount;
                $bill->temp_customer=null;
                $bill->PaidAmount = $PaidAmount;
                $bill->Remain = $PaidAmount - $TotalAmount;
                // 'dateInsert'=>$date,
                // 'type'=>'sell',
                // 'money_id'=>$money
                $money = Moneys::where('id', $money)->first();
                // $money->cach -= $primaryPaidAmount;
                $money->cach += $PaidAmount;
                $money->save();
                // $bill=Bill::create([
                //     'accounts_id'=>$create->id,
                //     'user_id'=>$validatedData['arr'][0]['user_id'],
                //     'total'=>$TotalAmount,
                //     'PaidAmount'=>$PaidAmount,
                //     'Remain'=>$PaidAmount-$TotalAmount,
                //     'dateInsert'=>$date,
                //     'type'=>'sell',
                //     'money_id'=>$money,
                // ]);
                // $reportinbill=Report::create([
                //     // 'isdelete'=>0,
                //     // 'user_id'=>$validatedData['arr'][0]['user_id'],
                //     // 'cash',
                //     // 'discription' => 'By Bill Number of ' . strval($bill->id),
                //     // 'amount'=>$TotalAmount-$PaidAmount,
                //     // 'date_created'=>$date,
                //     // 'type'=>'withdraw',
                //     // 'account_id'=>$create->id,
                // ]);
                $reportinbill = Report::where('bill_id', $Bill_id)->first();
                if ($reportinbill) {
                    $reportinbill->amount = $TotalAmount - $PaidAmount;
                    $reportinbill->account_id = $create->id;
                    $reportinbill->save();
                } else {
                    $reportinbill = Report::create([
                        'isdelete' => 0,
                        'user_id' => $validatedData['arr'][0]['user_id'],
                        'discription' => 'By Bill Number of ' . strval($bill->id),
                        'amount' => $TotalAmount - $PaidAmount,
                        'date_created' => $date,
                        'type' => 'withdraw',
                        'account_id' => $create->id,
                        'bill_id' => $create->bill_id
                    ]);
                }
                // return response()->json([
                //     'message' => 'Report created successfully',
                //     // 'report' => $report->id,
                // ], 201);
                // $$$$$$$$$$
                foreach ($validatedData['arr'] as $data) {
                    // $data = $this->getData($row);
                    if (!$validatedData['arr'][0]['bill_id']) {
                        $this->AddItem($data);
                        $data['bill_id'] = $bill->id;
                        try {
                            if (isset($data['dateInsert'])) {
                                // Remove 'Z' and convert to a format that Carbon can parse
                                $data['dateInsert'] = rtrim($data['dateInsert'], 'Z') . '+00:00';
                            }
                            if (isset($data['expiry_date'])) {
                                // Remove 'Z' and convert to a format that Carbon can parse
                                $data['expiry_date'] = rtrim($data['expiry_date'], 'Z') . '+00:00';
                            }
                            $report = Sell::create($data);
                            // return response()->json([
                            //     'message' => 'Report created successfully',
                            //     'bill' => $bill,
                            //     'belance'=>new BelanceResource($create),
                            //     'mainbelance'=>new BelanceResource($product),
                            //     'moneys'=>new MoneyResource($money)
                            // ], 201);
                        } catch (Exception $e) {
                            return response()->json([
                                'error' => 'Failed to create report'
                            ], 500);
                        }

                    }else{
                        $this->DeleteItem($data);
                    }
                }
                return response()->json([
                    'message' => 'Report created successfully',
                    'bill' => $bill,
                    'belance' => new BelanceResource($create),
                    'mainbelance' => new BelanceResource($product),
                    'moneys' => new MoneyResource($money)
                ], 201);
            } else {
                $create = Belances::create([
                    'type_id' => $money,
                    'user_id' => $validatedData['arr'][0]['user_id'],
                    'belance' => $PaidAmount - $TotalAmount,
                    'account_id' => 1,
                ]);
                if($Accounts_id){
                    $prveBelance=Belances::where('id',$Accounts_id)->first();
                    $prevMainBelance = Belances::where('account_id', 1)
                    ->where('type_id', $prveBelance->type_id)
                    ->first();
                    $prevMainBelance->belance-=$primaryTotalAmount;
                    $prevMainBelance->save();
                    $prveBelance->belance+=$primaryTotalAmount - $primaryPaidAmount ;
                    $prveBelance->save();
                    $prveMoney=Moneys::where('id',$money)->first();
                    $prveMoney->cach-=$primaryTotalAmount - $primaryPaidAmount;
                    $prveMoney->save();
                    $reportinbill = Report::where('bill_id', $Bill_id)->first();
                    if ($reportinbill) {
                        $reportinbill->delete();
                    }
                }
                else{
                    $prevMainBelance = Belances::where('account_id', 1)
                    ->where('type_id', $prevMoneyId)
                    ->first();
                    $prevMainBelance->belance-=$primaryTotalAmount;
                    $prevMainBelance->save();
                    $prveMoney=Moneys::where('id',$money)->first();
                    $prveMoney->cach-=$primaryTotalAmount;
                    $prveMoney->save();
                }
                $bill = Bill::where('id', $Bill_id)->first();
                $product = Belances::where('account_id', 1)
                    ->where('type_id', $money)
                    ->first();
                $product->belance += $TotalAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
                // $product->belance -= $bill->total; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
                $product->save();
                $date = rtrim($validatedData['arr'][0]['dateInsert'], 'Z') . '+00:00';
                $reportinbill = Report::where('bill_id', $Bill_id)->first();
                if ($reportinbill) {
                    $reportinbill->amount = $TotalAmount - $PaidAmount;
                    $reportinbill->account_id = $create->id;
                    $reportinbill->save();
                } else {
                    $reportinbill = Report::create([
                        'isdelete' => 0,
                        'user_id' => $validatedData['arr'][0]['user_id'],
                        'discription' => 'By Bill Number of ' . strval($bill->id),
                        'amount' => $TotalAmount - $PaidAmount,
                        'date_created' => $date,
                        'type' => 'withdraw',
                        'account_id' => $create->id,
                        'bill_id' => $create->bill_id
                    ]);
                }


                $bill->money_id = $money;
                $money = Moneys::where('id', $money)->first();
                // $money->cach -= $Bill_id;
                $money->cach += $PaidAmount;
                $money->save();
                $bill->total = $TotalAmount;
                $bill->temp_customer=null;
                $bill->PaidAmount = $PaidAmount;
                $bill->Remain = $PaidAmount - $TotalAmount;
                // $bill->temp_customer=$CustomerName;
                $bill->accounts_id = $create->id;
                $bill->save();

                foreach ($validatedData['arr'] as $data) {
                    // $data = $this->getData($row);
                    if (!$data['bill_id']) {
                        $this->AddItem($data);
                        $data['bill_id'] = $bill->id;
                        try {
                            if (isset($data['dateInsert'])) {
                                // Remove 'Z' and convert to a format that Carbon can parse
                                $data['dateInsert'] = rtrim($data['dateInsert'], 'Z') . '+00:00';
                            }
                            if (isset($data['expiry_date'])) {
                                // Remove 'Z' and convert to a format that Carbon can parse
                                $data['expiry_date'] = rtrim($data['expiry_date'], 'Z') . '+00:00';
                            }
                            $report = Sell::create($data);
                            // return response()->json([
                            //     'message' => 'Report created successfully',
                            //     'bill' => $bill,
                            //     'belance'=>new BelanceResource($create),
                            //     'mainbelance'=>new BelanceResource($product),
                            //     'moneys'=>new MoneyResource($money)
                            // ], 201);
                        } catch (Exception $e) {
                            return response()->json([
                                'error' => 'Failed to create report'
                            ], 500);
                        }
                    }else{
                        $this->DeleteItem($data);
                    }
                    // $TotalAmount+=(int)$data['purchase_price']*(int)$data['qty'];
                }
            }
            return response()->json([
                'message' => 'Report created successfully',
                'bill' => $bill,
                'belance' => new BelanceResource($create),
                'mainbelance' => new BelanceResource($product),
                'moneys' => new MoneyResource($money)
            ], 201);
        } else {
            if($Accounts_id){
                $prveBelance=Belances::where('id',$Accounts_id)->first();
                $prevMainBelance = Belances::where('account_id', 1)
                ->where('type_id', $prveBelance->type_id)
                ->first();
                $prevMainBelance->belance-=$primaryTotalAmount;
                $prevMainBelance->save();
                $prveBelance->belance+=$primaryTotalAmount - $primaryPaidAmount ;
                $prveBelance->save();
                $prveMoney=Moneys::where('id',$money)->first();
                $prveMoney->cach-=$primaryTotalAmount - $primaryPaidAmount;
                $prveMoney->save();
                $reportinbill = Report::where('bill_id', $Bill_id)->first();
                if ($reportinbill) {
                    $reportinbill->delete();
                }
            }
            else{
                $prevMainBelance = Belances::where('account_id', 1)
                ->where('type_id', $prevMoneyId)
                ->first();
                $prevMainBelance->belance-=$primaryTotalAmount;
                $prevMainBelance->save();
                $prveMoney=Moneys::where('id',$money)->first();
                $prveMoney->cach-=$primaryTotalAmount;
                $prveMoney->save();
            }
            $product = Belances::where('account_id', 1)
                ->where('type_id', $money)
                ->first();
            // $product->belance -= 10;
            $money = Moneys::where('id', $money)->first();
            $bill = Bill::where('id', $Bill_id)->first();
            $product->belance += $TotalAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
            $product->save();
            // $money->cach -= $bill->total;
            $money->cach += $TotalAmount;
            $money->save();
            $date = rtrim($validatedData['arr'][0]['dateInsert'], 'Z') . '+00:00';
            $bill->total = $TotalAmount;
            $bill->PaidAmount = $PaidAmount;
            $bill->Remain = 0;
            $bill->temp_customer=null;
            $bill->temp_customer = $CustomerName;
            $bill->money_id = $money->id;
            $bill->save();
            foreach ($validatedData['arr'] as $data) {
                // $data = $this->getData($row);
                if (!$data['bill_id']) {
                    $data['bill_id'] = $bill->id;
                    $this->AddItem($data);
                    try {
                        if (isset($data['dateInsert'])) {
                            // Remove 'Z' and convert to a format that Carbon can parse
                            $data['dateInsert'] = rtrim($data['dateInsert'], 'Z') . '+00:00';
                        }
                        if (isset($data['expiry_date'])) {
                            // Remove 'Z' and convert to a format that Carbon can parse
                            $data['expiry_date'] = rtrim($data['expiry_date'], 'Z') . '+00:00';
                        }
                        $report = Sell::create($data);
                        return response()->json([
                            'message' => 'Report created successfully',
                            'bill' => $bill,
                            // 'belance'=>new BelanceResource($create),
                            'mainbelance' => new BelanceResource($product),
                            'moneys' => new MoneyResource($money)
                        ], 201);
                    } catch (Exception $e) {
                        return response()->json([
                            'error' => $e
                        ], 500);
                    }
                }else{
                    $this->DeleteItem($data);
                }
            }
            return response()->json([
                'message' => 'Report created successfully',
                'bill' => $bill,
                // 'belance'=>new BelanceResource($create),
                'mainbelance' => new BelanceResource($product),
                'moneys' => new MoneyResource($money)
            ], 201);
        }
        // return response()->json([
        //     'message' => 'Report created successfully',
        //     'bill' => $bill,
        //     'belance'=>new BelanceResource($create),
        //     'mainbelance'=>new BelanceResource($product),
        //     'moneys'=>new MoneyResource($moneys)
        // ], 201);

    }

    // حذف فروش
    public function destroy($id)
    {
        try {
            $sell = Sell::findOrFail($id);
            $sell->delete();
            return response()->json(['success' => true, 'message' => 'Sell was successfully deleted.'], 200);
        } catch (Exception $exception) {
            return response()->json(['success' => false, 'message' => 'Error occurred while deleting the sell.'], 500);
        }
    }

    // دریافت داده‌های درخواست
    protected function getData(Request $request)
    {
        $rules = [
            'money' => 'required',
            'item_id' => 'required',
            'accounts_id' => 'required',
            'qty' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'weight' => 'nullable|numeric|min:-9|max:9',
            'dateInsert' => '',
            'rate' => 'required|numeric|min:-2147483648|max:2147483647',
            'user_id' => 'required',
            'isdelete' => 'boolean',
            'purchase_price' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'sell_price' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'expiry_date' => '',
            'description' => 'nullable',
        ];

        $data = $request->validate($rules);
        $data['isdelete'] = $request->has('isdelete');

        return $data;
    }
}
