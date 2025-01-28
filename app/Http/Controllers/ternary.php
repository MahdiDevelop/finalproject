<?php
namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\ReportResource;  // Assuming you are using a Resource for serialization

class ternary extends Controller
{
    // Action to sum by money type
    public function index(Request $request)
    {
          // دریافت پارامتر moneytype از درخواست
          $moneytype = $request->query('moneytype');
        
          // بررسی اگر moneytype ارسال نشده باشد
          if (!$moneytype) {
              return response()->json(['error' => 'moneytype parameter is required'], 400);
          }
          
          // محاسبه مجموع amount با فیلتر بر اساس نوع پول (moneytype) و isdelete = false
          $totalAmount = Report::whereHas('account', function ($query) use ($moneytype) {
              $query->where('type_id', $moneytype); // فیلتر بر اساس type در جدول account
          })
          ->where('isdelete', false) // فیلتر بر اساس isdelete
          ->count('amount'); // جمع کل ستون amount
          
          // بازگشت پاسخ به صورت JSON
          return response()->json(['total_amount' => $totalAmount]);
    }

    // Action to get the last report ID

    // Action to check balance by account id
    public function checkBalance(Request $request)
    {
        $accountId = $request->query('id');

        if (!$accountId) {
            return response()->json(['error' => 'id parameter is required'], 400);
        }

        $report = Report::where('account_id', $accountId)->first();

        if ($report) {
            return response()->json(['find' => new ReportResource($report)]);
        } else {
            return response()->json(['find' => '']);
        }
    }
}
