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

Route::get('/dashboard', [BinanceController::class, 'dashboard'])->name('dashboard');

Route::get('/funding-rates', [BinanceController::class, 'fundingRates']);

Route::get('/start', [BinanceController::class, 'start']);
Route::get('/end', [BinanceController::class, 'end']);

Route::any('/calculate-profit', [BinanceController::class, 'calculateProfit'])->name('calculate-profit');
Route::get('/funding-rates', [BinanceController::class, 'fundingRates'])->name('funding-rates');

Route::get('/buy', [BinanceController::class, 'buy'])->name('buy');

require __DIR__.'/auth.php';
