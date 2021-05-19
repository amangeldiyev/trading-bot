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

        $result = $binance->market()->getPremiumIndex([
            //'symbol'=>'BTCUSDT',
        ]);

        $highest_funding = 0;
        $symbol = '';

        foreach ($result as $coin) {
            if ($highest_funding < $coin['lastFundingRate']) {
                $highest_funding = $coin['lastFundingRate'];
                $symbol = $coin['symbol'];
            }

            dump($coin['symbol'] . " with funding rate " . $coin['lastFundingRate']);
        }
        
        dump("$symbol with highest funding $highest_funding");
    }

    public function fundingRates()
    {
        $binance = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        $days = 180;

        $investment = 10000 * 0.97;

        // $investment = 6093;

        $result = $binance->market()->getFundingRate([
            'symbol' => 'BTCUSDT',
            'limit' => $days * 3
        ]);

        $total = 0;

        foreach ($result as $rate) {
            $total += $rate['fundingRate'];
        }

        $return = $total * $investment * 0.8;

        dd(($return + $investment - $investment * 0.00223) * 1);
    }

    public function start()
    {
        $binance = new Binance(config('services.binance.api'), config('services.binance.secret'));
        $binance_futures = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        // Opening spot position
        $result = $binance->trade()->postOrder([
            'symbol'=>'SUSHIUSDT',
            'side'=>'BUY',
            'type'=>'MARKET',
            'quantity'=>'1',
        ]);

        // Opening futures position
        $result = $binance_futures->trade()->postOrder([
            'symbol'=>'SUSHIUSDT',
            'side'=>'SELL',
            'type'=>'MARKET',
            'positionSide' => 'Short',
            'quantity'=>'1',
        ]);
    }

    /**
    * Description
    *
    * @return void
    */
    public function end()
    {
        $binance = new Binance(config('services.binance.api'), config('services.binance.secret'));
        $binance_futures = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        // Closing spot position
        $result = $binance->trade()->postOrder([
            'symbol'=>'SUSHIUSDT',
            'side'=>'SELL',
            'type'=>'MARKET',
            'quantity'=>'1',
        ]);

        // Closing futures position
        $result = $binance_futures->trade()->postOrder([
            'symbol'=>'SUSHIUSDT',
            'side'=>'BUY',
            'type'=>'MARKET',
            'positionSide' => 'Short',
            "closePosition" => true,
            'quantity'=>'1',
        ]);
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
