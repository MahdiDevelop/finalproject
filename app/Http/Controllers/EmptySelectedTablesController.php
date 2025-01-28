<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class EmptySelectedTablesController extends Controller
{
    public function emptyTables(Request $request)
    {
        $tablesToEmpty = $request->input('tables', []);
    
        // غیرفعال کردن foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
        Cache::flush(); // Clear the cache before starting operations
        $mahdi = [];
    
        if (in_array('accounts', $tablesToEmpty)) {
            DB::table('accounts')->delete(); // Delete all rows
            DB::statement("ALTER TABLE accounts AUTO_INCREMENT = 1;"); // Reset auto-increment
        }
    
        if (in_array('money', $tablesToEmpty)) {
            DB::table('moneys')->delete(); // Delete all rows
            DB::statement("ALTER TABLE moneys AUTO_INCREMENT = 1;"); // Reset auto-increment
        }
    
        if (in_array('balance', $tablesToEmpty)) {
            // غیرفعال کردن foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
            // پاک‌سازی جدول
            DB::statement('DELETE FROM belances;');
        
            // ریست کردن AUTO_INCREMENT
            DB::statement('ALTER TABLE belances AUTO_INCREMENT = 1;');
        
            // فعال کردن مجدد foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    
        if (in_array('report', $tablesToEmpty)) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // غیرفعال کردن محدودیت‌های کلید خارجی
        
            // حذف جداول وابسته به ترتیب صحیح
            DB::table('purchases')->delete();
            DB::statement("ALTER TABLE purchases AUTO_INCREMENT = 1;");
            DB::table('sells')->delete();
            DB::statement("ALTER TABLE sells AUTO_INCREMENT = 1;");
            DB::table('bills')->delete();
            DB::statement("ALTER TABLE bills AUTO_INCREMENT = 1;");
            DB::table('reports')->delete();
            DB::statement("ALTER TABLE reports AUTO_INCREMENT = 1;");
            DB::table('item_types')->delete();
            DB::statement("ALTER TABLE item_types AUTO_INCREMENT = 1;");
            DB::table('items')->delete();
            DB::statement("ALTER TABLE items AUTO_INCREMENT = 1;");
        
            DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // فعال کردن مجدد محدودیت‌ها
        }
        
        // فعال کردن مجدد foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    
        return response()->json(['message' => 'Selected tables have been emptied and ID sequences reset.'], 204);
    }
    
}
