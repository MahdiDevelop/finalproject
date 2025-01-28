<?php

use App\Http\Controllers\api\BelanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UserActivityLogController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\MoneyController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ItemTypeController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\SellController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\testc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Validation\ValidationException;

use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/test',[ testc::class,'index']);
// Route::post('/register', function (Request $request) {
//     $request->validate([
//         'name' => 'required|string|max:255',
//         'email' => 'required|string|email|max:255|unique:users',
//         'password' => 'required|string|min:8',
//     ]);

//     $user = User::create([
//         'name' => $request->name,
//         'email' => $request->email,
//         'password' => Hash::make($request->password),
//     ]);

//     return response()->json(['message' => 'User registered successfully']);
// });
// Route::post('/login', function (Request $request) {
//     // echo'hi';
//     $request->validate([
//         'email' => 'required|string|email',
//         'password' => 'required|string',
//     ]);
//     $user = User::where('email', $request->email)->first();

//     if (! $user || ! Hash::check($request->password, $user->password)) {
//         throw ValidationException::withMessages([
//             'email' => ['The provided credentials are incorrect.'],
//         ]);
//     }

//     $token = $user->createToken($request->email)->plainTextToken;

//     return response()->json(['token' => '$token']);
// });

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware('auth:sanctum')->post('/logout', function (Request $request) {
//     $request->user()->tokens()->delete();

//     return response()->json(['message' => 'Logged out successfully']);
// });

// Route::get('/api/users', [UserController::class ,'index']);
// Route::get('/api/useractivity', [UserActivityLogController::class,'index']);
// Route::get('/api/accounts', [AccountController::class, 'index']);
// Route::get('/api/money', [MoneyController::class,'index']);
// Route::post('/api/belance', [BelanceController::class,'index']);
// Route::post('/api/hi', [BelanceController::class,'pst']);
// Route::get('/api/report', [ReportController::class,'index']);
// Route::get('/api/item', [ItemController::class,'index']);
// Route::get('/api/itemtype', [ItemTypeController::class,'index']);
// Route::get('/api/stock', [StockController::class,'index']);
// Route::get('/api/puchase', [PurchaseController::class,'index']);
// Route::get('/api/sell', [SellController::class,'index']);
// Route::get('/api/settings', [SettingsController::class,'index']);
