<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Settings;
use Illuminate\Http\Request;
use Exception;

class SettingsController extends Controller
{
    // لیست تنظیمات
    public function index()
    {
        $settingsObjects = Settings::get();
        return response()->json($settingsObjects);
    }

    // ایجاد تنظیم جدید
    public function store(Request $request)
    {
        $data = $this->getData($request);
        $settings = Settings::create($data);
        return response()->json(['id' => $settings->id, 'message' => 'Settings created successfully'], 201);
    }

    // نمایش یک تنظیم خاص
    public function show($id)
    {
        $settings = Settings::findOrFail($id);
        return response()->json($settings);
    }
    
    // ویرایش یک تنظیم
    public function update($id, Request $request)
{
    // بررسی اینکه آیا فایل ارسال شده است
    $data = $this->getData($request);
    // if ($request->hasFile('company_pic')) {
        //     $newImage = $request->file('company_pic');
    //     // اعتبارسنجی فایل
    //     $request->validate([
    //         'company_pic' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //          // محدودیت‌هایی برای فایل
    //         ]);
    //         $data['company_pic'] =asset('storage/' .  $newImage->store('company_pic', 'public'));
    // }
    if ($request->hasFile('company_pic')) {
        // دریافت فایل
        $file = $request->file('company_pic');
        
        // تعیین نام فایل (می‌توانید نام فایل را تغییر دهید)
        $fileName = time() . '.' . $file->getClientOriginalExtension();
        
        // ذخیره فایل در پوشه public/national_id_pictures
        $file->move(public_path('company_pic'), $fileName);
        
        // ساخت آدرس کامل برای فایل
        $data['company_pic'] = asset('company_pic/' . $fileName);
    }

    // فایل ارسال شده را دریافت کنید
    
    // ادامه کار ذخیره فایل در مسیر مشخص
    
    $settings = Settings::where('id',$id)->first();
    $settings->update($data);
    return response()->json(['settings' => $settings]);

}

    
    // حذف یک تنظیم
    public function destroy($id)
    {
        try {
            $settings = Settings::findOrFail($id);
            $settings->delete();
            return response()->json(['message' => 'Settings deleted successfully']);
        } catch (Exception $exception) {
            return response()->json(['error' => 'Unexpected error occurred while trying to process your request'], 500);
        }
    }

    // دریافت داده‌ها از درخواست
    protected function getData(Request $request)
    {
        $rules = [
            'language' => 'nullable|string|min:0|max:255',
            'date' => 'nullable|string|min:0|max:255',
            'company_pic' => 'nullable',
            'company_name' => 'nullable|string|min:0|max:255',
            'description' => 'nullable',
            'address' => 'nullable',
            'phone' => 'nullable|string|min:0|max:255',
            'email' => 'nullable|string|min:0|max:255',
        ];

        return $request->validate($rules);
    }
}
