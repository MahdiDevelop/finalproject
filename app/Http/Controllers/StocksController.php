<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\User;
use App\Models\Stock;
use Illuminate\Http\Request;
use Exception;

class StocksController extends Controller
{
    // نمایش لیست موجودی‌ها
    public function index(Request $request)
    {
        $perPage = $request->query('perPage', 10); // مقدار پیش‌فرض 10
        // $stocks = Stock::with('item', 'user');
        $stocks = Stock::with('item', 'user','item.type');
        // $items = item::with('type', 'user')->get();
        $search = $request->query('query');
        if($search){
            $data = Stock::with('item','item.type');
            $data=$data->where('name','like', "%$search%")->get();
    return response()->json($data);
        }
        $stock = $stocks->orderBy('dateInsert', 'desc')->paginate($perPage);

            // بازگشت داده‌ها همراه با اطلاعات صفحه‌بندی
            return response()->json([
                'data' => $stock,  // داده‌ها
                'total' => $stock->total(),  // تعداد کل رکوردها
                'current_page' => $stock->currentPage(),  // صفحه فعلی
                'per_page' => $stock->perPage(),  // تعداد آیتم‌ها در هر صفحه
                'last_page' => $stock->lastPage(),  // آخرین صفحه
            ], 200);

        // return response()->json($stocks);
    }

    // ایجاد موجودی جدید
    public function store(Request $request)
    {
        // $data = $this->getData($request);
        // $stock = Stock::create($data);
        
        $data = $this->getData($request);
        if (isset($data['dateInsert'])) {
            // Remove 'Z' and convert to a format that Carbon can parse
            $data['dateInsert'] = rtrim($data['dateInsert'], 'Z') . '+00:00';
        }
        $stock = Stock::create($data);
        return response()->json(['id' => $stock->id, 'message' => 'Stock created successfully'], 201);
    }

    // نمایش یک موجودی خاص
    public function show($id)
    {
        $stock = Stock::with('item', 'user')->findOrFail($id);
        return response()->json($stock);
    }

    // ویرایش یک موجودی
    public function update($id, Request $request)
    {
        $data = $this->getData($request);
        $stock = Stock::findOrFail($id);
        $stock->update($data);
        return response()->json(['message' => 'Stock updated successfully']);
    }

    // حذف یک موجودی
    public function destroy($id)
    {
        try {
            $stock = Stock::findOrFail($id);
            $stock->delete();
            return response()->json(['message' => 'Stock deleted successfully']);
        } catch (Exception $exception) {
            return response()->json(['error' => 'Unexpected error occurred while trying to process your request'], 500);
        }
    }

    // دریافت داده‌های درخواست
    protected function getData(Request $request)
    {
        $rules = [
            'item_id' => 'required',
            'qty' => 'nullable||string',
            'weight' => 'nullable|string',
            'dateInsert' => '',
            'rate' => 'required|numeric|min:-2147483648|max:2147483647',
            'user_id' => 'required',
            'isdelete' => 'boolean',
            'purchase_price' => 'nullable|string',
            'description' => 'nullable',
            'sell_price' => 'nullable|string', 
        ];

        $data = $request->validate($rules);
        $data['isdelete'] = $request->has('isdelete');

        return $data;
    }
}
