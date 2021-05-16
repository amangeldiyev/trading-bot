<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;
use Lin\Binance\Binance;
use Lin\Binance\BinanceFuture;

class BinanceController extends Controller
{

    protected $api;

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $binance = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        $result = $binance->market()->getKlines([
            'symbol' => 'BTCUSDT',
            'interval' =>  '1m',
            'limit' => 10
        ]);

        dd(array_slice($result, -2, 1)[0][4]);
    }

    public function portfolio()
    {
        # code...
    }

    public function goal()
    {
        $bank = 200;
        $iterations = 200;
        $monthly_deposit = 50;

        for ($i=1; $i < $iterations; $i++) { 
            if ($i % 30 == 0) {
                $bank += $monthly_deposit;
                dump("Depositing $monthly_deposit on iteration $i");
            }

            $pnl = $bank * 0.02;

            $bank += $pnl;

            dump("$i: Bank should be $bank with pnl $pnl");
        }

        return $bank;
    }

    public function strategy()
    {
        $price = 58000;
        $amount = 0.005;
        $total_amount = $amount;
        $price_interval = 2000;

        $average_price = $this->averageDown($price, $amount, $price - $price_interval, $amount);
        $price -= $price_interval;
        $total_amount += $amount;

        for ($i=0; $i < 3; $i++) {
            $average_price = $this->averageDown($average_price, $amount, $price - $price_interval, $amount);
            $price -= $price_interval;
            $total_amount += $amount;
            dump("buy $amount btc for $price");
        }

        $margin = $amount * $average_price;

        dump("$total_amount btc with average price of $average_price");
        dump("total margin of $margin");
    }

    public function averageDown($first_price, $first_amount, $second_price, $second_amount)
    {
        return ($first_price * $first_amount + $second_price * $second_amount) / ($first_amount + $second_amount);
    }
}
