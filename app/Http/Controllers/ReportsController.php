<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\BelanceResource;
use App\Models\Belance;
use App\Models\Moneys;
use App\Models\User;
use App\Models\Report;
use App\Models\Belances;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\ReportResource;
use App\Http\Resources\MoneyResource;

use Exception;

class ReportsController extends Controller
{
    // لیست گزارش‌ها
    public function index(Request $request)
    {
        // دریافت فیلترها
        $startDateStr = $request->query('startDate' || false);
        $endDateStr = $request->query('endDate' || false);
        $isDelete = $request->query('delete');
        $type = $request->query('type');
        $report = $request->query('report' || false);
        $moneyId = $request->query('moneyid');
        $accountId = $request->query('AccountId');
        $perPage = $request->query('perPage', 10); // مقدار پیش‌فرض 10

        // آغاز ساخت کوئری
        // $query = Report::query();
        $query = Report::with('user', 'account.type');
        // فیلتر بر اساس تاریخ
        // use Carbon\Carbon;
        if ($startDateStr && $endDateStr) {
            // تبدیل تاریخ‌ها به فرمت صحیح با استفاده از Carbon
            $startDate = Carbon::parse($startDateStr)->startOfDay(); // شروع روز
            $endDate = Carbon::parse($endDateStr)->endOfDay(); // پایان روز

            // اعمال فیلتر در درخواست پایگاه داده
            $query->whereBetween('date_created', [$startDate, $endDate]);
            // return response()->json($query->get()); // دریافت نتایج و بازگشت به صورت JSON
        }
        // فیلتر بر اساس شناسه‌های حساب‌ها (در قالب JSON)
        if ($accountId) {
            // تبدیل رشته دریافتی به آرایه از شناسه‌ها
            $accountIds=[];
            $accountIds = explode(',', $accountId); // تبدیل رشته به آرایه با جداکننده ویرگول
            $accountIds = array_map('intval', $accountIds); // تبدیل تمام مقادیر به نوع عددی (integer)
            
            // اعمال فیلتر بر اساس شناسه‌ها
            $query->whereIn('account_id', $accountIds); // فیلتر کردن داده‌ها بر اساس شناسه‌ها
            return response()->json($query->get());
        }
        
        // دریافت نتایج و بازگشت به صورت JSON
                //         // فیلتر بر اساس نوع
        if ($type !== "all") {
            $query->where('type', strval($type));
        } else {

        }
        // فیلتر بر اساس وضعیت حذف
        // if ($isDelete) {
        $query->where('isdelete', intval($isDelete));
        // }

        // فیلتر بر اساس شناسه پول
        if ($moneyId) {
            // $query->whereHas('account', function ($q) use ($moneyId) {
            //     $q->where('type_id', $moneyId);
            // });

            $query->whereHas('account', function ($query) use ($moneyId) {
                $query->where('type_id', $moneyId);
            });
        }
        // اجرای کوئری با صفحه‌بندی
        $reports = $query->paginate($perPage);

        // بازگشت داده‌ها همراه با اطلاعات صفحه‌بندی
        return response()->json([
            'data' => ReportResource::collection($reports),  // داده‌ها
            'total' => $reports->total(),  // تعداد کل رکوردها
            'current_page' => $reports->currentPage(),  // صفحه فعلی
            'per_page' => $reports->perPage(),  // تعداد آیتم‌ها در هر صفحه
            'last_page' => $reports->lastPage(),  // آخرین صفحه
        ], 200);
    }

    // ذخیره یک گزارش جدید
    public function store(Request $request)
    {
        $data = $this->getData($request);
        try {
            if (isset($data['date_created'])) {
                // Remove 'Z' and convert to a format that Carbon can parse
                $data['date_created'] = rtrim($data['date_created'], 'Z') . '+00:00';
            }
            $report = Report::create($data);
            $belance = Belances::where('id', $report->account_id)->first();
            $money = Moneys::where('id', $belance->type_id)->first();
            if ($report->type === 'deposite') {
                $belance->belance += $report->amount;
                $money->cach += $report->amount;
            } else {
                $belance->belance -= $report->amount;
                $money->cach -= $report->amount;
            }
            $money->ontransaction = 1;
            $belance->ontransaction = 1;
            $money->save();
            $belance->save();

            return response()->json([
                'moneys' => new MoneyResource($money), // اصلاح شده
                'report_belance' => $belance,
                'belance' => new BelanceResource($belance), // اصلاح شده
                'report' => $report->id,
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'error' => $e
            ], 500);
        }
    }
    // نمایش یک گزارش
    public function show($id)
    {
        $report = Report::with('user', 'account')->find($id);

        if ($report) {
            return response()->json($report, 200);
        } else {
            return response()->json(['error' => 'Report not found'], 404);
        }
    }

    // به‌روزرسانی یک گزارش
    public function update($id, Request $request)
    {
        $type = $request->get('type');
        $data = $this->getData($request);

        if ($type === 'delete') {
            $report = Report::find($id);
            if ($report) {
                $report->isdelete = 1;
                $report->save();
                $belance = Belances::where('id', $report->account_id)->first();
                $money = Moneys::where('id', $belance->type_id)->first();
                if ($report->type === 'deposite') {
                    $belance->belance -= $report->amount;
                    $money->cach -= $report->amount;
                } else {
                    $belance->belance += $report->amount;
                    $money->cach += $report->amount;
                }
                $money->ontransaction = 1;
                $belance->ontransaction = 1;
                $money->save();
                $belance->save();

                return response()->json([
                    'moneys' => new MoneyResource($money), // اصلاح شده
                    'report_belance' => $belance,
                    'belance' => new BelanceResource($belance), // اصلاح شده
                    'report' => $report->id,
                ], 201);
            }
        } else if ($type === 'change') {
            $report = Report::where('id', $id)->first();
            if ($report) {
                $belance = Belances::where('id', $report->account_id)->first();
                $money = Moneys::where('id', $belance->type_id)->first();
                $secondbelance = Belances::where('id', $data['account'])->first();
                if ($report->type === 'deposite') {
                    $belance->belance -= $report->amount;
                    $money->cach -= $report->amount;
                    $secondbelance->belance += $data['amount'];
                    $money->cach += $data['amount'];
                } else {
                    $belance->belance += $report->amount;
                    $money->cach += $report->amount;
                    $secondbelance->belance -= $data['amount'];
                    $money->cach -= $data['amount'];
                }
                $report->update([
                    'user_id'=>$data['user_id'],
                    'cash'=>$data['cash'],
                    'discription'=>$data['discription'],
                    'amount'=>$data['amount'],
                    // 'date_created'=>$data['date'],
                    'type'=>$data['type'],
                    'account_id'=>$data['account'],
                            ]);
                $money->save();
                $belance->save();
                $secondbelance->save();
                return response()->json([
                    'moneys' => new MoneyResource($money), // اصلاح شده
                    'report_belance' => $belance,
                    'secondreport_belance' => $secondbelance,
                    'belance' => new BelanceResource($belance), // اصلاح شده
                    'secondbelance' => new BelanceResource($secondbelance), // اصلاح شده
                    'report' => new ReportResource($report),
                ], 201);
            }
        } else {
            $report = Report::where('id', $id)->first();
            if ($report) {
                $belance = Belances::where('id', $report->account_id)->first();
                $money = Moneys::where('id', $belance->type_id)->first();
                if ($report->type === 'deposite') {
                    $belance->belance -= $report->amount;
                    $money->cach -= $report->amount;
                    $belance->belance += $data['amount'];
                    $money->cach += $data['amount'];
                } else {
                    $belance->belance += $report->amount;
                    $money->cach += $report->amount;
                    $belance->belance -= $data['amount'];
                    $money->cach -= $data['amount'];
                }
                $report->update([
                'user_id'=>$data['user_id'],
                'cash'=>$data['cash'],
                'discription'=>$data['discription'],
                'amount'=>$data['amount'],
                // 'date_created'=>$data['date_created'],
                'type'=>$data['type'],
                'account_id'=>$data['account'],
                ]);
                $money->save();
                $belance->save();
                // $secondbelance->save();
                return response()->json([
                    'moneys' => new MoneyResource($money), // اصلاح شده
                    'report_belance' => $belance,
                    'belance' => new BelanceResource($belance), // اصلاح شده
                    'report' => new ReportResource($report),
                ], 201);
            }
        }

    }

    // حذف یک گزارش
    public function destroy($id)
    {
        try {
            $report = Report::find($id);

            if ($report) {
                $report->delete();
                return response()->json(['message' => 'Report deleted successfully'], 200);
            } else {
                return response()->json(['error' => 'Report not found'], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to delete report'
            ], 500);
        }
    }

    //     public function sumByMoneyType(Request $request)
    // {
    //     // $moneytype = $request->query('moneytype');
    //     // if (!$moneytype) {
    //     //     return response()->json(['error' => 'moneytype parameter is required'], 400);
    //     // }

    //     // $totalAmount = \DB::table('reports')
    //     //     ->where('account_type', $moneytype)
    //     //     ->where('isdelete', false)
    //     //     ->sum('amount');

    //     return response()->json(['total_amount' => "hi"]);
    // }

    public function sumByMoneyType(Request $request)
    {
        // $moneytype = $request->query('moneytype');

        // if (!$moneytype) {
        //     return response()->json(['error' => 'moneytype parameter is required'], 400);
        // }

        // $totalAmount = YourModel::where('account_type', $moneytype)
        //     ->where('isdelete', false)
        //     ->sum('amount'); // Use sum instead of aggregate for direct total calculation

        return response()->json('total_amount');
    }


    public function getLastReportId()
    {
        $lastReport = \DB::table('reports')->latest('id')->first();
        if (!$lastReport) {
            return response()->json(['error' => 'No reports found']);
        }

        return response()->json(['last_report_id' => $lastReport->id]);
    }
    public function checkBalance(Request $request)
    {
        $accountId = $request->query('id');
        if (!$accountId) {
            return response()->json(['error' => 'id parameter is required'], 400);
        }

        $report = \DB::table('reports')->where('account_id', $accountId)->first();

        if ($report) {
            return response()->json(['find' => $report]);
        } else {
            return response()->json(['find' => '']);
        }
    }

    // اعتبارسنجی و دریافت داده‌های درخواست
    protected function getData(Request $request)
    {
        $rules = [
            'isdelete' => 'nullable|boolean',
            'user_id' => 'nullable|exists:users,id',
            'cash' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'discription' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:-9223372036854775800|max:9223372036854775800',
            'date_created' => 'nullable',
            'type' => 'nullable|string|max:255',
            'account_id' => 'nullable|numeric|min:0',
            'account' => 'nullable|numeric|min:0',
            'MainDW.isdelete' => 'nullable|boolean',
            'MainDW.user_id' => 'nullable|exists:users,id',
            'MainDW.cash' => 'nullable|numeric|min:-2147483648|max:2147483647',
            'MainDW.discription' => 'nullable|string|max:255',
            'MainDW.amount' => 'nullable|numeric|min:-9223372036854775800|max:9223372036854775800',
            'MainDW.date_created' => '',
            'MainDW.type' => 'nullable|string|max:255',
            'MainDW.account_id' => 'nullable|numeric|min:0',
        ];

        // 'id'=>$this->id,   
        // 'isdelete'=>$this->isdelete,
        // 'user'=>$this->user_id,
        // 'user_name'=>$this->user->name,
        // 'cash'=>$this->cash,
        // 'discription'=>$this->discription,
        // 'amount'=>$this->amount,
        // 'date_created'=>$this->date_created,
        // 'type'=>$this->type,
        // 'moneyid'=>$this->account->type->id,
        // 'moneyType'=>$this->account->type->name,
        // 'account'=>$this->account_id,
        // 'customer'=>$this->account->account->name,
        $data = $request->validate($rules);
        // $data['isdelete'] = $request->has('isdelete');

        return $data;
    }
}
