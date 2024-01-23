<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\MpesaResponseController;
use App\Http\Controllers\AnalysisController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/analytics', function () {
    return view('analytics');
});

/*
 *My operation endpoints
*/
Route::get('/mp/accesstoken',[MpesaController::class, 'getAccessToken']);
Route::get('/qrcode',[MpesaController::class, 'dynamicQRcode']);
Route::post('/b2c',[MpesaController::class, 'b2c']);
Route::post('/stkpush',[MpesaController::class, 'stkPush']);
Route::post('/b2b',[MpesaController::class, 'b2b']);
Route::post('/buy-goods-services',[MpesaController::class, 'buyGoodsAndServices']);
Route::post('/paybill',[MpesaController::class, 'paybill']);

/*
 *Analysis endpoints
*/
Route::get('analytics', [AnalysisController::class, 'transactionStatus']);
