<?php

use App\Http\Controllers\BelanceController;
use App\Http\Controllers\TransformationController;
use Doctrine\Inflector\Rules\Transformations;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AccountsController;
use App\Http\Controllers\BelancesController;
use App\Http\Controllers\ItemsController;
use App\Http\Controllers\ItemTypesController;
use App\Http\Controllers\MoneysController;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SellsController;
use App\Http\Controllers\ternary;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StocksController;
use App\Http\Controllers\UserActivityLogsController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\EmptySelectedTablesController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

use App\Http\Controllers\Api\AuthController;

Route::middleware('auth:sanctum')->group(function () {
    Route::resource('customers', AccountsController::class);
    Route::resource('belance', BelancesController::class);
    Route::resource('item', ItemsController::class);
    Route::resource('itemtype', ItemTypesController::class);
    Route::resource('money', MoneysController::class);
    Route::resource('purchase', PurchasesController::class);
    
    Route::resource('report', ReportsController::class);
    Route::get('reports/sum_by_money_type', [ternary::class, 'index']); 
    Route::get('reports/get_last_report_id',[ ReportsController::class,'getLastReportId']);
    Route::get('report/checkbelance', [ReportsController::class,'checkBalance']);
    Route::resource('sell', SellsController::class);
    Route::resource('settings', SettingsController::class);
    Route::resource('stock', StocksController::class);
    Route::resource('user', UsersController::class);
    Route::resource('transformations', TransformationController::class);
    Route::post('/empty-selected-tables', [EmptySelectedTablesController::class, 'emptyTables'])->middleware('auth:api');
});
// routes/web.php یا routes/api.php
Route::get('getImage/{image}', function ($image) {
    // مسیر تصویر را به دست می‌آوریم
    $path = public_path("company_pic/{$image}");

    // چک می‌کنیم که آیا فایل وجود دارد یا خیر
    if (!file_exists($path)) {
        return response()->json(['error' => 'Image not found'], 404);
    }

    // ارسال تصویر به صورت مستقیم (در اینجا فایل در همان مسیر public قرار دارد)
    return response()->file($path);
});


Route::post('/login', [AuthController::class, 'login']); 
// Route::group([
//     'prefix' => 'users',
// ], function () {
//     Route::get('/', [UsersController::class, 'index'])
//          ->name('users.user.index');
//     Route::get('/create', [UsersController::class, 'create'])
//          ->name('users.user.create');
//     Route::get('/show/{user}',[UsersController::class, 'show'])
//          ->name('users.user.show')->where('id', '[0-9]+');
//     Route::get('/{user}/edit',[UsersController::class, 'edit'])
//          ->name('users.user.edit')->where('id', '[0-9]+');
    // Route::post('/', [UsersController::class, 'store'])
    //      ->name('users.user.store');
//     Route::put('user/{user}', [UsersController::class, 'update'])
//          ->name('users.user.update')->where('id', '[0-9]+');
//     Route::delete('/user/{user}',[UsersController::class, 'destroy'])
//          ->name('users.user.destroy')->where('id', '[0-9]+');
// });
Route::group([
    'prefix' => 'user_activity_logs',
], function () {
//     Route::get('/', [UserActivityLogsController::class, 'index'])
//          ->name('user_activity_logs.user_activity_log.index');
//     Route::get('/create', [UserActivityLogsController::class, 'create'])
//          ->name('user_activity_logs.user_activity_log.create');
//     Route::get('/show/{userActivityLog}',[UserActivityLogsController::class, 'show'])
//          ->name('user_activity_logs.user_activity_log.show')->where('id', '[0-9]+');
//     Route::get('/{userActivityLog}/edit',[UserActivityLogsController::class, 'edit'])
//          ->name('user_activity_logs.user_activity_log.edit')->where('id', '[0-9]+');
//     Route::post('/', [UserActivityLogsController::class, 'store'])
//          ->name('user_activity_logs.user_activity_log.store');
//     Route::put('user_activity_log/{userActivityLog}', [UserActivityLogsController::class, 'update'])
//          ->name('user_activity_logs.user_activity_log.update')->where('id', '[0-9]+');
//     Route::delete('/user_activity_log/{userActivityLog}',[UserActivityLogsController::class, 'destroy'])
//          ->name('user_activity_logs.user_activity_log.destroy')->where('id', '[0-9]+');
});
