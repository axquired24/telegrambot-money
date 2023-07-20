<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DuitController;
use App\Http\Controllers\ViewController;

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
Route::post('bot/sendreport', [DuitController::class, 'sendReport']);
Route::get('bot/daily', [DuitController::class, 'recordDailyMessages']);
Route::get('db/parse', [DuitController::class, 'parseDailyUpdate']);
Route::get('webhook/set', [DuitController::class, 'setWebHook']);
Route::get('webhook/unset', [DuitController::class, 'unsetWebHook']);
Route::post('webhook/callback', [DuitController::class, 'webhookCallback']);

Route::get('masterdata', [ViewController::class, 'getMasterData']);
Route::post('money/list', [ViewController::class, 'getMoneyData']);
Route::get('invalid/list', [ViewController::class, 'getInvalidChat']);
Route::post('invalid/solve', [ViewController::class, 'updateInvalidChat']);

Route::post('trx/edit', [DuitController::class, 'editMoneyTrack']);
Route::post('trx/add', [DuitController::class, 'addMoneyTrack']);
Route::post('trx/delete', [DuitController::class, 'deleteMoneyTrack']);
