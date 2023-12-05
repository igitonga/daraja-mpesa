<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MpesaResponseController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::post('/c2b/stkpush/callback/{id}', [MpesaController::class, 'c2bStkpushCallback']);
Route::post('/b2b/callback/{id}', [MpesaController::class, 'b2bCallback']);
Route::post('/b2c/queue/{id}', [MpesaController::class, 'b2cQueue']);
Route::post('/b2c/result/{id}', [MpesaController::class, 'b2cResult']);




