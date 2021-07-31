<?php

use App\Http\Controllers\BinanceController;
use Illuminate\Support\Facades\Route;

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

Route::get('/dashboard', [BinanceController::class, 'fundingRates'])->name('dashboard');

Route::get('/funding-rates', [BinanceController::class, 'fundingRates'])->name('funding-rates');
Route::match(['get', 'post'], '/total-funding-rate', [BinanceController::class, 'totalFundingRate'])->name('total-funding-rate');

Route::match(['get', 'post'], '/position/open', [BinanceController::class, 'open'])->name('open');
Route::match(['get', 'post'], '/position/close', [BinanceController::class, 'close'])->name('close');

require __DIR__.'/auth.php';
