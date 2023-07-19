<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ViewController;
use App\Http\Controllers\DuitController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [ViewController::class, 'index']);
Route::get('/v2/{slug?}', [ViewController::class, 'v2']);
Route::post('/edit', [DuitController::class, 'editMoneyTrack']);
Route::post('/add', [DuitController::class, 'addMoneyTrack']);
Route::post('/delete', [DuitController::class, 'deleteMoneyTrack']);
Route::get('/invalidchat', [ViewController::class, 'invalidChat']);
Route::post('/invalidchat/solved', [ViewController::class, 'solveInvalidChat']);
