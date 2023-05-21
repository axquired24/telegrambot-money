<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DuitController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// Route::get('money/check', [DuitController::class, 'index']);
// Route::get('bot/check', [DuitController::class, 'botCheck']);
Route::get('bot/sendreport', [DuitController::class, 'sendReport']);
Route::get('bot/daily', [DuitController::class, 'recordDailyMessages']);
Route::get('db/parse', [DuitController::class, 'parseDailyUpdate']);
