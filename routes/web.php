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

Route::get('/start', [BinanceController::class, 'start']);
Route::get('/end', [BinanceController::class, 'end']);

require __DIR__.'/auth.php';
