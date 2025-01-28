<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ItemType;
use App\Models\User;
use Illuminate\Http\Request;
use Exception;

class ItemTypesController extends Controller
{
    // /**
    //  * Display a listing of the item types.
    //  *
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    public function index()
    {
        $itemTypes = ItemType::with('user')->get();
        return response()->json($itemTypes);
    }

    // /**
    //  * Store a new item type in the storage.
    //  *
    //  * @param Illuminate\Http\Request $request
    //  *
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    public function store(Request $request)
{
    $data = $this->getData($request);

    if ($request->hasFile('picture')) {
        

        $file = $request->file('picture');
            
            // تعیین نام فایل (می‌توانید نام فایل را تغییر دهید)
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            
            // ذخیره فایل در پوشه public/profile_pictures
            $file->move(public_path('picture_item'), $fileName);
            
            // ساخت آدرس کامل برای فایل
            $data['picture'] = asset('picture_item/' . $fileName);


        // ذخیره فایل و برگرداندن مسیر فایل
    }

    $itemType = ItemType::create($data);

    return response()->json([
        'id' => $itemType->id,
        'message' => 'Item created successfully.'
    ], 201); // 201 Created
}


    // /**
    //  * Display the specified item type.
    //  *
    //  * @param int $id
    //  *
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    public function show($id)
    {
        $itemType = ItemType::with('user')->findOrFail($id);
        return response()->json($itemType);
    }

    // /**
    //  * Update the specified item type in the storage.
    //  *
    //  * @param int $id
    //  * @param Illuminate\Http\Request $request
    //  *
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    public function update($id, Request $request)
    {
        $data = $this->getData($request);
        
        $itemType = ItemType::findOrFail($id);
        
    if ($request->hasFile('picture')) {
        // ذخیره فایل و برگرداندن مسیر فایل
        $filePath = $request->file('picture')->store('picture_item', 'public');
        
        // ذخیره مسیر کامل فایل به صورت مستقیم در دیتابیس
        $data['picture'] = '/storage/' . $filePath;}
        $itemType->update($data);

        return response()->json($itemType);
    }

    // /**
    //  * Remove the specified item type from the storage.
    //  *
    //  * @param int $id
    //  *
    //  * @return \Illuminate\Http\JsonResponse
    //  */
    public function destroy($id)
    {
        try {
            $itemType = ItemType::findOrFail($id);
            $itemType->delete();

            return response()->json(['message' => 'Item Type was successfully deleted.']);
        } catch (Exception $exception) {
            return response()->json(['error' => 'Unexpected error occurred while trying to process your request.'], 500);
        }
    }

    // /**
    //  * Get the request's data from the request.
    //  *
    //  * @param Illuminate\Http\Request $request
    //  * @return array
    //  */
    protected function getData(Request $request)
    {
        $rules = [
            'name' => 'nullable|string|max:255',
            'picture' => 'nullable|file',
            'isdelete' => 'boolean',
            'user_id' => 'nullable|exists:users,id', // Ensure user_id exists in users table
            'measuring' => 'nullable|string|max:255',
        ];

        $data = $request->validate($rules);
        $data['isdelete'] = $request->has('isdelete');

        return $data;
    }
}
