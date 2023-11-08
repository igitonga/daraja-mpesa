<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MpesaController;
use App\Http\Controllers\MpesaResponseController;
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

/*
 *My user endpoints
*/
Route::get('/mp/accesstoken',[MpesaController::class, 'getAccessToken']);
Route::get('/qrcode',[MpesaController::class, 'dynamicQRcode']);
Route::get('/c2b',[MpesaController::class, 'c2b']);
Route::get('/b2c',[MpesaController::class, 'b2c']);
Route::post('/stkpush',[MpesaController::class, 'stkPush']);
Route::get('/store', [MpesaController::class, 'store']);

/*
 *Callback endpoints
*/
Route::post('/callback/queue', [MpesaController::class, 'queueTimeOut']);
Route::post('/callback/result', [MpesaController::class, 'result']);
Route::post('/response/callback/{id}', [MpesaController::class, 'responseCallback']);


