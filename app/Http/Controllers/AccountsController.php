<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountsResource;
use App\Models\Accounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AccountsController extends Controller
{
    /**
     * Display a listing of the accounts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {   
        $search = $request->query('query');
        $query = Accounts::with('user');
        $startDate = $request->query('startDate');
        $endDate = $request->query('endDate');
        $delete = $request->query('delete');
        $type = $request->query('type');
        $moneyId = $request->query('moneyid');
        $accountId = $request->query('AccountId');
        if($search){
            $query = Accounts::where('name', 'like', "%$search%")->get();
            return response()->json($query);
        }
        // فیلتر تاریخ
        // if ($startDate && $endDate) {
        //     $query->whereBetween('date_created', [$startDate, $endDate]);
        // }

        // فیلتر بر اساس AccountId
        // if ($accountId) {
        //     $accountIds = json_decode($accountId, true);
        //     $query->whereIn('id', $accountIds);
        // }

        // فیلتر نوع حساب
        // if ($type) {
        //     $query->where('type', $type);
        // }

        // فیلتر حذف‌شده
        if ($delete) {
            $query->where('isdelete', $delete);
        }

        // فیلتر moneyId
        // if ($moneyId) {
        //     $query->where('type', $moneyId);
        // }

        // $accountsObjects = $query->paginate(25); جدا کردن به 25 دانه
        $accountsObjects = $query->get();  

        return response()->json($accountsObjects);
    }

    public function store(Request $request)
    {
        $data = $this->getData($request);
    
        // ذخیره‌سازی فایل‌های آپلود شده و ایجاد آدرس کامل
        if ($request->hasFile('profile_picture')) {
            // دریافت فایل
            $file = $request->file('profile_picture');
            
            // تعیین نام فایل (می‌توانید نام فایل را تغییر دهید)
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            
            // ذخیره فایل در پوشه public/profile_pictures
            $file->move(public_path('profile_pictures'), $fileName);
            
            // ساخت آدرس کامل برای فایل
            $data['profile_picture'] = asset('profile_pictures/' . $fileName);
        }
    
        if ($request->hasFile('national_id_picture')) {
            // دریافت فایل
            $file = $request->file('national_id_picture');
            
            // تعیین نام فایل (می‌توانید نام فایل را تغییر دهید)
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            
            // ذخیره فایل در پوشه public/national_id_pictures
            $file->move(public_path('national_id_pictures'), $fileName);
            
            // ساخت آدرس کامل برای فایل
            $data['national_id_picture'] = asset('national_id_pictures/' . $fileName);
        }
    
        // ذخیره داده‌ها در دیتابیس
        $account = Accounts::create($data);
        $accountId = $account->id;
    
        return response()->json([
            'id' => $accountId,
            'customer'=>$account,
            'message' => 'Account created successfully.'
        ], 201);
    }
    

    public function show($id)
    {
        $account = Accounts::with('user')->findOrFail($id);
       
        return response()->json($account);
    }


    // public function update($id, Request $request)
    // {
    //     // اعتبارسنجی ورودی‌ها
    
    //     $account = Accounts::findOrFail($id);
    
    //     $data = $request->only(['name', 'father_name', 'national_id_number', 'phone_number', 'whatsup_number', 'addresss','profile_picture']);
    
    //     // پردازش تصاویر
    //     if ($request->File('profile_picture')) {
    //         // حذف تصویر قبلی
    //         // if ($account->profile_picture) {
    //         //     Storage::disk('public')->delete($account->profile_picture);
    //         // }
    //         // ذخیره تصویر جدید
    //         $data['profile_picture'] = $request->file('profile_picture')->store('uploads', 'public');
    //     }
    
    //     if ($request->hasFile('national_id_picture')) {
    //         // حذف تصویر قبلی
    //         if ($account->national_id_picture) {
    //             Storage::disk('public')->delete($account->national_id_picture);
    //         }
    //         // ذخیره تصویر جدید
    //         $data['national_id_picture'] = $request->file('national_id_picture')->store('uploads', 'public');
    //     }
    
    //     // به‌روزرسانی حساب کاربری
    //     $account->update($data);
    
    //     return response()->json(['message' => 'Account updated successfully.', 'account' => $data], 200);
    // }
    
    public function update($id, Request $request)
    {
        // بررسی اینکه آیا فایل ارسال شده است
        $data = $this->getData($request);

        if ($request->hasFile('profile_picture')) {
            $request->validate([
                'profile_picture' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                 // محدودیت‌هایی برای فایل
                ]);

            $file = $request->file('profile_picture');
            
            // تعیین نام فایل (می‌توانید نام فایل را تغییر دهید)
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            
            // ذخیره فایل در پوشه public/profile_pictures
            $file->move(public_path('profile_pictures'), $fileName);
            
            // ساخت آدرس کامل برای فایل
            $data['profile_picture'] = asset('profile_pictures/' . $fileName);

            
            // $newImage = $request->file('profile_picture');
            // اعتبارسنجی فایل
                // $data['profile_picture'] =asset('storage/' .  $newImage->store('profile_pictures', 'public'));
        }

        if ($request->hasFile('national_id_picture')) {
            
            // $newImage = $request->file('national_id_picture');
            // اعتبارسنجی فایل
            $request->validate([
                'national_id_picture' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                 // محدودیت‌هایی برای فایل
                ]);
                $file = $request->file('national_id_picture');
            
                // تعیین نام فایل (می‌توانید نام فایل را تغییر دهید)
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                
                // ذخیره فایل در پوشه public/national_id_pictures
                $file->move(public_path('national_id_pictures'), $fileName);
                
                // ساخت آدرس کامل برای فایل
                $data['national_id_picture'] = asset('national_id_pictures/' . $fileName);
            
                // $data['national_id_picture'] =asset('storage/' .  $newImage->store('national_id_pictures', 'public'));
        }
        // فایل ارسال شده را دریافت کنید
    
        // ادامه کار ذخیره فایل در مسیر مشخص
        
        $settings = Accounts::findOrFail($id);
        $settings->update($data);
    
        return response()->json(['customer' => $settings]);
    }


    /**
     * Update the specified account in the storage.
     *
     * @param int $id
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    // public function update($id, Request $request)
    // {
    //     $data = $this->getData($request);
    //     $account = Accounts::findOrFail($id);

    //     // به‌روزرسانی فایل‌های آپلود شده
    //     if ($request->hasFile('profile_picture')) {
    //         $data['profile_picture'] = $this->uploadFile($request->file('profile_picture'));
    //     }

    //     if ($request->hasFile('national_id_picture')) {
    //         $data['national_id_picture'] = $this->uploadFile($request->file('national_id_picture'));
    //     }

    //     $account->update($data);

    //     return response()->json([
    //         'message' => 'Account updated successfully.'
    //     ]);
    // }

// public function update($id, Request $request)
// {
//     // دریافت داده‌ها از درخواست
//     $data = $this->getData($request);

//     // پیدا کردن حساب مورد نظر از دیتابیس
//     $account = Accounts::findOrFail($id);

//     if ($request->hasFile('profile_picture')) {
//         // $request->validate([
//         //     'profile_picture' => 'file|mimes:image/jpeg,png,jpg|max:50048',
//         // ]);
//         // ذخیره فایل و برگرداندن مسیر فایل
//         $filePath = $request->file('profile_picture')->store('profile_pictures');
//         // ساخت آدرس کامل برای فایل
//         $data['profile_picture'] = asset('storage/' . $filePath);
//     }

//     if ($request->hasFile('national_id_picture')) {
//         // $request->validate([
//         //     'national_id_picture' => 'image|mimes:image/jpeg,png,jpg|max:50048',
//         // ]);
//         // ذخیره فایل و برگرداندن مسیر فایل
//         $filePath = $request->file('national_id_picture')->store('national_id_pictures', 'public');
//         // ساخت آدرس کامل برای فایل
//         $data['national_id_picture'] = asset('storage/' . $filePath);
//     }

//     // به‌روزرسانی اطلاعات حساب
//     try {
//         $account->update($data);

//         // بازگشت پاسخ موفقیت
//         return response()->json([
//             'message' => 'Account updated successfully.',
//             'account' =>$this->getData($request),  // اطلاعات به‌روزرسانی‌شده را هم ارسال می‌کنیم
//         ]);
//     } catch (\Exception $e) {
//         // مدیریت خطا و ارسال پاسخ مناسب
//         return response()->json([
//             'message' => 'Failed to update account.',
//             'error' => $e->getMessage()
//         ], 500); // کد 500 برای خطای سرور
//     }
// }


    /**
     * Remove the specified account from the storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $account = Accounts::findOrFail($id);
        $account->delete();

        return response()->json([
            'message' => 'Account deleted successfully.'
        ]);
    }

    // تابع ذخیره‌سازی فایل
    // private function uploadFile($file)
    // {
    //     // نام یکتا برای فایل با استفاده از timestamp و نام اصلی
    //     $fileName = time() . '_' . $file->getClientOriginalName();
        
    //     // ذخیره فایل در پوشه 'uploads' در storage
    //     $path = $file->storeAs('uploads', $fileName, 'public');

    //     return $path; // مسیر فایل ذخیره شده
    // }

    protected function getData(Request $request)
    {
        $rules = [
            'ontransaction' => 'boolean',
            'isdelete' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
            'name' => 'nullable|string|min:1|max:255',
            'date_created' => 'nullable|date_format:Y-m-d',
            'father_name' => 'nullable|string|max:255',
            'national_id_number' => 'nullable|string',
            'phone_number' => 'nullable|string',
            'whatsup_number' => 'nullable|string',
            'address' => 'nullable|string|max:255',
            'profile_picture' => 'nullable|max:500000000000000000048', // محدودیت فرمت و سایز
            'national_id_picture' => 'nullable|max:5000000000000000000048', // محدودیت فرمت و سایز
        ];

        return $request->validate($rules);
    }
}
