<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\BelanceResource;
use App\Http\Resources\MoneyResource;
use App\Models\Accounts;
use App\Models\Belances;
use App\Models\Bill;
use App\Models\Item;
use App\Models\User;
use App\Models\Purchase;
use App\Models\Report;
use App\Models\Moneys;
use App\Models\Stock;
use Illuminate\Http\Request;
use Exception;

class PurchasesController extends Controller
{
    /**
     * Display a listing of the purchases.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $isDelete = $request->query('delete');
        $perPage = $request->query('perPage', 10); // مقدار پیش‌فرض 10

        $query = Purchase::with(['bill.money', 'user','bill.accounts.account','stock']) // بارگذاری روابط مرتبط
            ->where('isdelete', intval($isDelete))
            ->orderBy('dateInsert', 'desc');
    
        $reports = $query->get()
            ->groupBy(function ($item) {
                return $item->bill->id; // گروه‌بندی بر اساس ID بیل
            })
            ->map(function ($group) {
                return [
                    'bill' => $group->first()->bill, // اطلاعات بیل
                    'money' => $group->first()->bill->money, // اطلاعات مانی مربوط به بیل
                    'purchase' => $group->toArray(), // تبدیل سل‌ها به آرایه
                ];
            })
            ->values();
            $currentPage = $request->query('page', 1);
            $paginatedData = $reports->slice(($currentPage - 1) * $perPage, $perPage)->toArray();
        return response()->json([
            'data' => $paginatedData,  // داده‌ها
            'total' => $reports->count(),  // تعداد کل رکوردها
            'current_page' => $currentPage,  // صفحه فعلی
            'per_page' => $perPage,  // تعداد آیتم‌ها در هر صفحه
            'last_page' =>  ceil($reports->count() / $perPage),  // آخرین صفحه
        ], 200);
    }

    /**
     * Store a new purchase in the storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $Exesting = $request->query('Exesting');
        $money=$request->query('money');
        $accounts_id=$request->query('accounts_id');
        $PaidAmount=$request->query('PaidAmount');
        $TotalAmount=$request->query('TotalAmount');
        $CustomerName=$request->query('CustomerName');
        $DateInsert=$request->query('DateInsert');
        $create=null;
        $validatedData = $request->validate([
            'arr'=>'required|array',
            'arr.*.stocks_id' => '',
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
            'arr.*.tepm_customer' => 'nullable',
        ]);
        if (isset($DateInsert)) {
        $DateInsert = rtrim($DateInsert, 'Z') . '+00:00';
        }
        if($Exesting==='ok'){
            // if($accounts_id==='no'){
            //     $customer=Accounts::create([
            //         'name'=>$CustomerName,
            //         'user_id'=>$validatedData['arr'][0]['user_id'],
            //     ]);
            //     $accounts_id=$customer->id;
            // }
            // $itemsdata=[];
            $create=Belances::where('type_id',$money)->where('account_id',$accounts_id)->first();
            if ($create){      
                $create->belance +=$TotalAmount-$PaidAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
                $create->save();
                // if (isset($DateInsert)) {
                $date = rtrim($validatedData['arr'][0]['dateInsert'], 'Z') . '+00:00';
                    // }
                $bill=Bill::create([
                    'accounts_id'=>$create->id,
                    'user_id'=>$validatedData['arr'][0]['user_id'], 
                    'total'=>$TotalAmount, 
                    'PaidAmount'=>$PaidAmount,
                    'Remain'=>$PaidAmount-$TotalAmount,
                    'dateInsert'=>$date,
                    'type'=>'purchase',
                    'money_id'=>$money,
                ]);
                $reportinbill=Report::create([
                    'isdelete'=>0,
                    'user_id'=>$validatedData['arr'][0]['user_id'],
                    'cash',
                    'discription' => 'By Bill Number of ' . strval($bill->id),
                    'amount'=>$TotalAmount-$PaidAmount,
                    'date_created'=>$date,
                    'type'=>'deposite',
                    'account_id'=>$create->id,
                    // 'transformation',
                ]);
                $product = Belances::where('account_id', 1)
                       ->where('type_id', $money)
                       ->first();
                        $product->belance -=$TotalAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
                        $product->save();
                        $money=Moneys::where('id',$money)->first();
                        $money->cach-=$PaidAmount;
                        $money->save();
                        // return response()->json([
                        //     'message' => 'Report created successfully',
                        //     // 'report' => $report->id,
                        // ], 201);
                        // $$$$$$$$$$
                        foreach ($validatedData['arr']  as $data){
                            // $data = $this->getData($row);
                            // $itemsdata.add($data);
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
                                $report = Purchase::create($data);
                                // return response()->json([
                                //     'message' => 'Report created successfully',
                                //     'bill'=>$bill,
                                //     'belance'=>new BelanceResource($create),
                                //     'mainbelance'=>new BelanceResource($product)
                                // ], 201);
                            } catch (Exception $e) {
                                return response()->json([
                                    'error' => $e
                                ], 500);
                            }
                        }
                        return response()->json([
                            'message' => 'Report created successfully addbelance',
                            'bill'=>$bill,
                            'belance'=>new BelanceResource($create),
                            'mainbelance'=>new BelanceResource($product),
                            'moneys'=>new MoneyResource($money)

                        ], 201);
            }
            else{
                $product = Belances::where('account_id', 1)
                ->where('type_id', $money)
                ->first();
                 $product->belance -=$TotalAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
                 $product->save();
                $create=Belances::create([
                    'type_id'=>$money,
                    'user_id'=>$validatedData['arr'][0]['user_id'],
                   'belance'=>$TotalAmount-$PaidAmount, 
                   'account_id'=>$accounts_id,
                ]);
                $date = rtrim($validatedData['arr'][0]['dateInsert'], 'Z') . '+00:00';
                $bill=Bill::create([
                    'accounts_id'=>$create->id,
                    'user_id'=>$validatedData['arr'][0]['user_id'], 
                    'total'=>$TotalAmount, 
                    'PaidAmount'=>$PaidAmount,
                    'Remain'=>$PaidAmount-$TotalAmount,
                    'dateInsert'=>$date,
                    'type'=>'purchase',
                    'money_id'=>$money
                ]);
                $reportinbill=Report::create([
                    'isdelete'=>0,
                    'user_id'=>$validatedData['arr'][0]['user_id'],
                    'cash',
                    'discription'=>'By Bill Number of ' . strval($bill->id),
                    'amount'=>$TotalAmount-$PaidAmount,
                    'date_created'=>$date,
                    'type'=>'deposite',
                    'account_id'=>$create->id,
                    // 'transformation',
                ]);
                $money=Moneys::where('id',$money)->first();
                        $money->cach-=$PaidAmount;
                        $money->save();
                foreach ($validatedData['arr']  as $data){
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
                        $report = Purchase::create($data);
                        // return response()->json([
                        //     'message' => 'Report created successfully',
                        //     'bill'=>$bill,
                        //     'belance'=>new BelanceResource($create),
                        //     'mainbelance'=>new BelanceResource($product)
                        // ], 201);
                    } catch (Exception $e) {
                        return response()->json([
                            'error' => $e
                        ], 500);
                    }
                }
                return response()->json([
                    'message' => 'Report created successfully',
                    'bill'=>$bill,
                    'belance'=>new BelanceResource($create),
                    'mainbelance'=>new BelanceResource($product),
                     'moneys'=>new MoneyResource($money)

                ], 201);
            }
             }
        else{
            $product = Belances::where('account_id', 1)
            ->where('type_id', $money)
            ->first();
             $product->belance -=$TotalAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
             $product->save();
             $date = rtrim($validatedData['arr'][0]['dateInsert'], 'Z') . '+00:00';
            //  $data = rtrim($data['dateInsert'], 'Z') . '+00:00';
             $bill=Bill::create([
                'id'=>1,
                'temp_cusomter'=>$CustomerName,
                'user_id'=>$validatedData['arr'][0]['user_id'], 
                'total'=>$TotalAmount,
                'PaidAmount'=>$TotalAmount,
                'Remain'=>$TotalAmount,
                'dateInsert'=>$date,
                'type'=>'purchase',
                'money_id'=>$money,
                'temp_customer'=>$CustomerName
            ]);
             $money=Moneys::where('id',$money)->first();
             $money->cach-=$TotalAmount;
             $money->save();
            
            foreach ($validatedData['arr']  as $data){
                // $data = $this->getData($row);
                $data['bill_id']=$bill->id;
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
                    $report = Purchase::create($data);
            // return response()->json([
            //     'message' => 'Report created successfully',
            //     'bill'=>$bill,
            //     // 'belance'=>new BelanceResource($create),
            //     'mainbelance'=>new BelanceResource($product)
            // ], 201);
                } catch (Exception $e) {
                    return response()->json([
                        'error' => 'Failed to create report'
                    ], 500);
                }
            }
            return response()->json([
                'message' => 'Report created successfully',
                'bill'=>$bill,
                // 'belance'=>new BelanceResource($create),
                'mainbelance'=>new BelanceResource($product),
                'moneys'=>new MoneyResource($money)

            ], 201);
        }
        // return response()->json([
        //     'message' => 'Report created successfully',
        //     'bill'=>$bill,
        //     'belance'=>new BelanceResource($create),
        //     'mainbelance'=>new BelanceResource($product)
        // ], 201);
    }
    protected function AddItem($data){
        $product = Item::where('id', $data['stocks_id'])->first();
        $product->qty += (int)$data['qty']; // Assuming the quantity field in the stock table
        $product->weight = $data['weight']; // Assuming the quantity field in the stock table
        $product->rate = $data['rate']; // Assuming the quantity field in the stock table
        $product->save();
        return $product;
    }
    protected function DeleteItem($data){
        $product = Item::where('id', $data['stocks_id'])->first();
        $product->qty -= (int)$data['qty']; // Assuming the quantity field in the stock table
        // $product->weight = $data['weight']; // Assuming the quantity field in the stock table
        // $product->rate = $data['rate']; // Assuming the quantity field in the stock table
        $product->save();
        // return $product;
    }
    

    /**
     * Display the specified purchase.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $purchase = Purchase::with('balance', 'user','stock.type')->findOrFail($id);

        return response()->json($purchase);
    }

    /**
     * Update the specified purchase in the storage.
     *
     * @param int $id
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update($id, Request $request)
    {
        $prevMoneyId = $request->query('prevMoney');
        $Bill_id = $request->query('Bill_id');
        $Exesting = $request->query('Exesting');
        $money=$request->query('money');
        $accounts_id=$request->query('accounts_id');
        $PaidAmount=$request->query('PaidAmount');
        $TotalAmount=$request->query('TotalAmount');
        $CustomerName=$request->query('CustomerName');
        $primaryPaidAmount = $request->query('primaryPaidAmount');
        $Accounts_id = $request->query('Accounts_id');
        $primaryTotalAmount = $request->query('primaryTotalAmount');
        $DateInsert=$request->query('DateInsert');
        $create=null;
        $validatedData = $request->validate([
            'arr'=>'required|array',
            'arr.*.stocks_id' => '',
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
            'arr.*.tepm_customer' => 'nullable',
        ]);
        if (isset($DateInsert)) {
        $DateInsert = rtrim($DateInsert, 'Z') . '+00:00';
        }
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
        $secondcreate = Belances::where('id', $Accounts_id)->first();
        if($Exesting==='ok'){
            // if($accounts_id==='no'){
            //     $customer=Accounts::create([
            //         'name'=>$CustomerName,
            //         'user_id'=>$validatedData['arr'][0]['user_id'],
            //     ]);
            //     $accounts_id=$customer->id;
            // }
            // $itemsdata=[];
            $create=Belances::where('type_id',$money)->where('account_id',$accounts_id)->first();
            if ($create){  
                $bill=Bill::where('id',$Bill_id)->first();
                $bill->accounts_id = $create->id;
                $bill->user_id = $validatedData['arr'][0]['user_id'];
                // $bill_total = $TotalAmount;
                $bill->temp_customer=null;
                $bill->PaidAmount = $PaidAmount;
                $bill->Remain = $PaidAmount - $TotalAmount;
                $bill->save();
                // $create->belance -= (int)$bill->total - (int)$bill->PaidAmount;
                $create->belance +=$TotalAmount-$PaidAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
                $create->save();
                // if (isset($DateInsert)) {
                $date = rtrim($validatedData['arr'][0]['dateInsert'], 'Z') . '+00:00';
                $product = Belances::where('account_id', 1)
                           ->where('type_id', $money)
                           ->first();
                            $product->belance -=$TotalAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
                            // $product->belance +=$bill->TotalAmount;
                            $product->save();
                            $money=Moneys::where('id',$money)->first();
                            // $money->cach+=$bill->PaidAmount;
                            $money->cach-=$PaidAmount;
                            $money->save();
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
                        'type' => 'deposite',
                        'account_id' => $create->id,
                        'bill_id' => $create->bill_id
                    ]);
                }
                        foreach ($validatedData['arr']  as $data){
                            // $data = $this->getData($row);
                            // $itemsdata.add($data);
                            if(!$data['bill_id']){
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
                                    $report = Purchase::create($data);
                                    // return response()->json([
                                    //     'message' => 'Report created successfully',
                                    //     'bill'=>$bill,
                                    //     'belance'=>new BelanceResource($create),
                                    //     'mainbelance'=>new BelanceResource($product)
                                    // ], 201);
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
                            'bill'=>$bill,
                            'belance'=>new BelanceResource($create),
                            'mainbelance'=>new BelanceResource($product),
                            'moneys'=>new MoneyResource($money)

                        ], 201);
            }
            else{
                $create=Belances::create([
                    'type_id'=>$money,
                    'user_id'=>$validatedData['arr'][0]['user_id'],
                   'belance'=>$TotalAmount-$PaidAmount, 
                   'account_id'=>$accounts_id,
                ]);
                $bill=Bill::where('id',$Bill_id)->first();
                $product = Belances::where('account_id', 1)
                ->where('type_id', $money)
                ->first();
                // $product->belance+=$bill->total;
                 $product->belance -=$TotalAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
                 $product->save();
                $date = rtrim($validatedData['arr'][0]['dateInsert'], 'Z') . '+00:00';
                // $bill=Bill::create([
                //     'accounts_id'=>$create->id,
                //     'user_id'=>$validatedData['arr'][0]['user_id'], 
                //     'total'=>$TotalAmount, 
                //     'PaidAmount'=>$PaidAmount,
                //     'Remain'=>$PaidAmount-$TotalAmount,
                //     'dateInsert'=>$date,
                //     'type'=>'purchase',
                //     'money_id'=>$money
                // ]);
                $money=Moneys::where('id',$money)->first();
                        $money->cach-=$PaidAmount;
                        // $money->cach=$bill->PaidAmount;
                        $money->save();
                $bill->total=$TotalAmount;
                $bill->Remain=$PaidAmount - $TotalAmount;
                $bill->accounts_id=$create->id;
            $bill->temp_customer = null;
                $bill->money_id=$money;
                $bill->save();
                // $reportinbill=Report::create([
                //     'isdelete'=>0,
                //     'user_id'=>$validatedData['arr'][0]['user_id'],
                //     'cash',
                //     'discription'=>'By Bill Number of '+strval($bill->id),
                //     'amount'=>$TotalAmount-$PaidAmount,
                //     'date_created'=>$date,
                //     'type'=>'deposite',
                //     'account_id'=>$create->id,
                //     // 'transformation',
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
                        'type' => 'deposite',
                        'account_id' => $create->id,
                        'bill_id' => $create->bill_id
                    ]);
                }
                foreach ($validatedData['arr']  as $data){
                    // $data = $this->getData($row);
                    if(!$data['bill_id']){
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
                            $report = Purchase::create($data);
                            // return response()->json([
                            //     'message' => 'Report created successfully',
                            //     'bill'=>$bill,
                            //     'belance'=>new BelanceResource($create),
                            //     'mainbelance'=>new BelanceResource($product)
                            // ], 201);
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
                    'bill'=>$bill,
                    'belance'=>new BelanceResource($create),
                    'mainbelance'=>new BelanceResource($product),
                     'moneys'=>new MoneyResource($money)

                ], 201);
            }
             }
        else{
            $bill=Bill::where('id',$Bill_id)->first();
            $bill->money_id = $money;
            $bill->total=$TotalAmount;
            $bill->PaidAmount=$PaidAmount;
            $bill->Remain=0;
            $bill->temp_customer = $CustomerName;
            $bill->accounts_id=null;
            $product = Belances::where('account_id', 1)
            ->where('type_id', $money)
            ->first();
            $money=Moneys::where('id',$money)->first();
            // $product->belance -=$TotalAmount; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
            // $product->belance +=$bill->total; // Assuming the quantity field in the stock table // Assuming the quantity field in the stock table
            $product->save();
             $date = rtrim($validatedData['arr'][0]['dateInsert'], 'Z') . '+00:00';
            //  $money->cach-=$TotalAmount;
             $money->cach+=$bill->total - $bill->PaidAmount;
             $money->save();
            $bill->save();
            foreach ($validatedData['arr']  as $data){
                // $data = $this->getData($row);
                $data['bill_id']=$bill->id;
                if($data['bill_id']){
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
                        $report = Purchase::create($data);
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
                'message' => 'Report created successfully tepm',
                'bill'=>$bill,
                // 'belance'=>new BelanceResource($create),
                'mainbelance'=>new BelanceResource($product),
                'moneys'=>new MoneyResource($money)

            ], 201);
        }
    }

    /**
     * Remove the specified purchase from the storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            $purchase->delete();

            return response()->json([
                'message' => 'Purchase successfully deleted.'
            ], 200);
        } catch (Exception $exception) {
            return response()->json([
                'error' => 'Unexpected error occurred while trying to process your request.'
            ], 500);
        }
    }

    /**
     * Get the request's data from the request.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    protected function getData(Request $request)
    {
        $rules = [
            'stocks_id' => '',
            'accounts_id' => '',
            'qty' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'weight' => 'nullable',
            'dateInsert' => '',
            'rate' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'user_id' => 'required',
            'isdelete' => 'nullable|boolean',
            'purchase_price' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'sell_price' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'expiry_date' => '',
            'description' => 'nullable',
            'tepm_customer' => 'nullable',
        ];

        $data = $request->validate($rules);

        $data['isdelete'] = $request->has('isdelete');

        return $data;
    }
}
