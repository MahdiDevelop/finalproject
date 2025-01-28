<?php

namespace App\Http\Controllers;

use App\Http\Resources\BelanceResource;
use App\Http\Resources\ReportResource;
use GuzzleHttp\Promise\Create;
use Illuminate\Http\Request;
use App\Models\Accounts;
use App\Models\Report;
use App\Models\User;
use App\Models\Belances;
use Carbon\Carbon;
use Exception;

class TransformationController extends Controller
{
    // لیست گزارش‌ها
    public function index(Request $request)
    {
        $perPage = (int) $request->query('perPage', 10);
        $currentPage = (int) $request->query('page', 1);
    
        // دریافت گزارش‌ها با روابط مورد نیاز و فیلتر نوع
        $query = Report::with(['user', 'account.type', 'account.account'])
            ->whereIn('type', ['from', 'to', 'com']);
    
        // گروه‌بندی و ساختاردهی داده‌ها به صورت مجموعه‌ای
        $groupedReports = $query->get()->groupBy('transformation')->map(function ($group) {
            return [
                'to' => $group->firstWhere('type', 'to') ? new ReportResource($group->firstWhere('type', 'to')) : null,
                'from' => $group->firstWhere('type', 'from') ? new ReportResource($group->firstWhere('type', 'from')) : null,
                'com' => $group->firstWhere('type', 'com') ? new ReportResource($group->firstWhere('type', 'com')) : null,
            ];
        })->filter(function ($group) {
            // حذف گروه‌هایی که همه فیلدهایشان خالی هستند
            return $group['to'] || $group['from'] || $group['com'];
        });
    
        // جمع‌آوری تعداد کل گزارش‌ها
        $total = $groupedReports->count();
    
        // صفحه‌بندی داده‌ها
        $paginatedReports = $groupedReports->forPage($currentPage, $perPage)->values();
    
        // استفاده از طولانی‌ترین صفحه‌بندی لاراول
        $groupedReportsPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedReports,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url()]
        );
    
        // ارسال پاسخ JSON
        return response()->json([
            'total' => $groupedReportsPaginator->total(),
            'current_page' => $groupedReportsPaginator->currentPage(),
            'per_page' => $groupedReportsPaginator->perPage(),
            'last_page' => $groupedReportsPaginator->lastPage(),
            'data' => $groupedReportsPaginator->items(),
        ], 200);
    }
    

          // ذخیره یک گزارش جدید
    public function store(Request $request)
    {
        $comesion = $request->get('complete');
        $data = $this->getData($request);
        if ($comesion === 'ok') {
            $belancefrom = Belances::where('id', $data['from_account_id'])->first();
            $belancefrom->belance -= $data['from_amount'];
            $belancefrom->save();
            // 
            $belanceto = Belances::where('id', $data['to_account_id'])->first();
            $belanceto->belance += $data['to_amount'];
            $belanceto->save();
            // 
            $belancecom = Belances::where('id', $data['com_account_id'])->first();
            $belancecom->belance += $data['from_amount'] - $data['to_amount'];
            $belancecom->save();
            $data['date_creation'] = rtrim($data['date_creation'], 'Z') . '+00:00';
            $report = Report::create([
                'user_id' => $data['user_id'],
                'discription' => $data['from_description'],
                'amount' => $data['from_amount'],
                'date_created' => $data['date_creation'],
                'type' => 'from',
                'account_id' => $data['from_account_id']
            ]);
            $new = Report::where('id', $report->id)->first();
            $new->transformation = $report->id;
            $new->save();
            // 
            $to = Report::create([
                'user_id' => $data['user_id'],
                'discription' => $data['to_description'],
                'amount' => $data['to_amount'],
                'date_created' => $data['date_creation'],
                'type' => 'to',
                'account_id' => $data['to_account_id'],
                'transformation' => $report->id,
            ]);
            $com = Report::create([
                'user_id' => $data['user_id'],
                'discription' => $data['com_description'],
                'amount' => $data['from_amount'] - $data['to_amount'],
                'date_created' => $data['date_creation'],
                'type' => 'com',
                'account_id' => $data['com_account_id'],
                'transformation' => $report['id'],
            ]);
            return response()->json(['bill'=>['from' => $report, 'to' => $to, 'com' => $com,], 'belancecom' => $belancecom, 'belanceto' => $belanceto, 'belancefrom' => $belancefrom, 'belancecom_report' => new BelanceResource($belancecom), 'belanceto_report' => new BelanceResource($belanceto), 'belancefrom_report' => new BelanceResource($belancefrom)]);
            // 
        } else {
            $belancefrom = Belances::where('id', $data['from_account_id'])->first();
            $belancefrom->belance -= $data['from_amount'];
            $belancefrom->save();
            // 
            $belanceto = Belances::where('id', $data['to_account_id'])->first();
            $belanceto->belance += $data['to_amount'];
            $belanceto->save();
            $data['date_creation'] = rtrim($data['date_creation'], 'Z') . '+00:00';

            // 
            $report = Report::create([
                'user_id' => $data['user_id'],
                'discription' => $data['from_description'],
                'amount' => $data['from_amount'],
                'type' => 'from',
                'date_created' => $data['date_creation'],
                'account_id' => $data['from_account_id']
            ]);
            $new = Report::where('id', $report->id)->first();
            $new->transformation = $report->id;
            $new->save();
            // 
            $to = Report::create([
                'user_id' => $data['user_id'],
                'discription' => $data['to_description'],
                'amount' => $data['to_amount'],
                'date_created' => $data['date_creation'],
                'type' => 'to',
                'account_id' => $data['to_account_id'],
                'transformation' => $report->id,
            ]);
            return response()->json(['bill'=>['from' => $report, 'to' => $to], 'belancefrom' => $belancefrom, 
            'belanceto' => $belanceto, 'belanceto_report' => new BelanceResource($belanceto),
             'belancefrom_report' => new BelanceResource($belancefrom)]);

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
        try {
            $data = $this->getData($request);
            $report = Report::find($id);

            if ($report) {
                $report->update($data);
                return response()->json([
                    'message' => 'Report updated successfully',
                    'report' => $report
                ], 200);
            } else {
                return response()->json(['error' => 'Report not found'], 404);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to update report'
            ], 500);
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
            'user_id' => 'required',
            'from_account_id' => 'required',
            'from_amount' => '',
            'from_description' => '',
            'to_account_id' => 'required',
            'to_amount' => '',
            'to_description' => '',
            'com_account_id' => '',
            'com_amount' => '',
            'com_description' => '',
            'date_creation' => '',
        ];
        $data = $request->validate($rules);
        return $data;
    }
}