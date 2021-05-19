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

        $days = 1009;

        $investment = 1000 * 1; // Deposit Comission.

        $result = $binance->market()->getFundingRate([
            'symbol' => 'ETHUSDT',
            'limit' => $days * 3
        ]);

        $total = 0;


        foreach ($result as $rates) {
            $total += $rates['fundingRate'];
        }

        $return = $total * $investment * 0.8; // 80% - spot, 20% - futures

        dd(($return + $investment - $investment * 0.00223) * 1); // buy/sell order fees: 0.223%
    }

    public function start()
    {
        $binance = new Binance(config('services.binance.api'), config('services.binance.secret'));
        $binance_futures = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        // Opening spot position
        //
        

        // Opening futures position
        $result = $binance_futures->trade()->postOrder([
            'symbol'=>'ETHUSDT',
            'side'=>'SELL',
            'type'=>'MARKET',
            'positionSide' => 'Short',
            'quantity'=>'0.0335',
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
            'symbol'=>'ETHUSDT',
            'side'=>'SELL',
            'type'=>'MARKET',
            'quantity'=>'0.0335',
        ]);

        // Closing futures position
        $result = $binance_futures->trade()->postOrder([
            'symbol'=>'ETHUSDT',
            'side'=>'BUY',
            'type'=>'MARKET',
            'positionSide' => 'Short',
            "closePosition" => true,
            'quantity'=>'0.0335',
        ]);
    }

    public function portfolio()
    {
        # code...
    }

    public function averageDown($first_price, $first_amount, $second_price, $second_amount)
    {
        return ($first_price * $first_amount + $second_price * $second_amount) / ($first_amount + $second_amount);
    }
}
