<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\StockResource;
use App\Models\ItemType;
use App\Models\User;
use App\Models\Item;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Http\JsonResponse;

class ItemsController extends Controller
{
    /**
     * Display a listing of the items.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('query');
        $perPage = $request->query('perPage', 10); // مقدار پیش‌فرض 10
        $stock = $request->query('stock');
        $items = item::with('type', 'user');

        if($search && $stock){
            $data = Item::with('type');
            $data=$data->where('qty','>','0');
            $data=$data->where('name','like', "%$search%")->get();
        return response()->json($data);
        }
        if($search){
            $data = Item::with('type', 'user')->get();
            return response()->json($data);
        }
        if($stock==='true'){
            $items = Item::with('type', 'user');
            $items=$items->where('qty','>',0)->get();
            return response()->json($items);

        }
        else{
            $items = Item::with('type', 'user');
        }
        $reports = $items->paginate($perPage);
        return response()->json([
            'data' => $reports->items(),  // داده‌ها
            'total' => $reports->total(),  // تعداد کل رکوردها
            'current_page' => $reports->currentPage(),  // صفحه فعلی
            'per_page' => $reports->perPage(),  // تعداد آیتم‌ها در هر صفحه
            'last_page' => $reports->lastPage(),  // آخرین صفحه
        ], 200);
    }

    /**
     * Show the form for creating a new item (not applicable for API).
     *
     * @return JsonResponse
     */
    public function create(): JsonResponse
    {
        // This method is typically not needed in API as the creation is handled directly through store.
        return response()->json(['message' => 'Create item not applicable.']);
    }

    /**
     * Store a new item in the storage.
     *
     * @param Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->getData($request);
    
        try {
            // اطمینان از اینکه تاریخ به فرمت مناسب تبدیل می‌شود
            if (isset($data['date_creation'])) {
                $data['date_creation'] = \Carbon\Carbon::parse($data['date_creation'])->format('Y-m-d H:i:s');
            }
    
            $item = Item::create($data);
    
            return response()->json([
                'message' => 'Item created successfully.',
                'item' => $item,
            ], 201); // HTTP 201 Created
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create item: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Display the specified item.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        $item = item::with('itemtype', 'user')->findOrFail($id);
        return response()->json($item);
    }

    /**
     * Show the form for editing the specified item (not applicable for API).
     *
     * @param int $id
     * @return JsonResponse
     */
    public function edit($id): JsonResponse
    {
        // This method is typically not needed in API as the editing is handled directly through update.
        return response()->json(['message' => 'Edit item not applicable.']);
    }

    /**
     * Update the specified item in the storage.
     *
     * @param int $id
     * @param Illuminate\Http\Request $request
     * @return JsonResponse
     */
    public function update($id, Request $request): JsonResponse
    {
        $data = $this->getData($request);
        try {
            if (isset($data['date_creation'])) {
                // Remove 'Z' and convert to a format that Carbon can parse
                $data['date_creation'] = rtrim($data['date_creation'], 'Z') . '+00:00';
            }
            // $report = item::create($data);
            // return response()->json([
            //     'message' => 'Report created successfully',
            //     'id' => $report->id,
            // ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create report'
            ], 500);
        }
        $item = item::findOrFail($id);
        $item->update($data);

        return response()->json(['message' => 'Item updated successfully.', 'item' => $item]);
    }

    /**
     * Remove the specified item from the storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        try {
            $item = item::findOrFail($id);
            $item->delete();

            return response()->json(['message' => 'Item deleted successfully.']);
        } catch (Exception $exception) {
            return response()->json(['error' => 'Unexpected error occurred while trying to process your request.'], 500);
        }
    }

    /**
     * Get the request's data from the request.
     *
     * @param Illuminate\Http\Request $request
     * @return array
     */
    protected function getData(Request $request): array
    {
        $rules = [
            'name' => 'nullable|string|max:255',
            'type_id' => 'nullable|numeric|min:0',
            'user_id' => '',
            'isdelete' => 'boolean',
            'description' => 'nullable|string',
            'date_creation' => '',
            'serial_number' => 'nullable|string|min:0|max:255',
            'rate'=>'nullable'
        ];

        $data = $request->validate($rules);
        $data['isdelete'] = $request->has('isdelete');

        return $data;
    }
}
