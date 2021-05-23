<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        return view('dashboard');
    }

    public function calculateProfit(Request $request)
    {
        $return = null;
        $symbol = $request->symbol;
        $bank = $request->bank;
        $start = $request->start;
        $days = $request->days;

        if ($request->isMethod('POST')) {
            
            $binance = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

            $startTime = Carbon::createFromFormat('Y-m-d', $request->start)->timestamp;

            $days = $request->days;

            $investment = $request->bank;

            $result = $binance->market()->getFundingRate([
                'symbol' => $request->symbol,
                'startTime' => $startTime,
                'limit' => $days * 3
            ]);

            $total = 0;

            foreach ($result as $rates) {
                $total += $rates['fundingRate'];
            }

            $interest = $total * $investment * 0.8; // 80% - spot, 20% - futures

            $return = $interest + $investment - $investment * 0.00223; // buy/sell order fees: 0.223%

        }

        return view('calculate-profit', compact('return', 'symbol', 'bank', 'start', 'days'));
    }

    public function fundingRates()
    {
        $binance = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        $result = $binance->market()->getPremiumIndex([
            //'symbol'=>'BTCUSDT',
        ]);

        $highest_funding = [
            'symbol' => 'BTCUSDT',
            'value' => 0
        ];

        $lowest_funding = [
            'symbol' => 'BTCUSDT',
            'value' => 0
        ];

        foreach ($result as $coin) {
            if ($highest_funding['value'] < $coin['lastFundingRate']) {
                $highest_funding['symbol'] = $coin['symbol'];
                $highest_funding['value'] = $coin['lastFundingRate'];
            }

            if ($lowest_funding['value'] > $coin['lastFundingRate']) {
                $lowest_funding['symbol'] = $coin['symbol'];
                $lowest_funding['value'] = $coin['lastFundingRate'];
            }
        }

        return view('funding-rates', compact('highest_funding', 'lowest_funding'));
    }

    public function buy()
    {
        $binance = new Binance(config('services.binance.api'), config('services.binance.secret'));
        $binance_futures = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        $spot_account = $binance->user()->getAccount();

        $spot_balance = Arr::where($spot_account['balances'], function ($value, $key) {
            return $value['asset'] == 'USDT';
        });

        dd($spot_balance);

        $funding_rates = $binance_futures->market()->getPremiumIndex();

        return view('buy', compact('spot_balance', 'funding_rates'));
    }

    public function start()
    {
        $binance = new Binance(config('services.binance.api'), config('services.binance.secret'));
        $binance_futures = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        $symbol = 'ETHUSDT';
        $quantity = '0.12';

        // Opening spot position
        $result = $binance->trade()->postOrder([
            'symbol' => $symbol,
            'side' => 'BUY',
            'type' => 'MARKET',
            'quantity' => $quantity,
        ]);
        

        // Opening futures position
        $result = $binance_futures->trade()->postOrder([
            'symbol' => $symbol,
            'side' => 'SELL',
            'type' => 'MARKET',
            'positionSide' => 'Short',
            'quantity' => $quantity,
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

        $symbol = 'BTCUSDT';
        $quantity = '0.01';

        // Closing spot position
        $result = $binance->trade()->postOrder([
            'symbol' => $symbol,
            'side' => 'SELL',
            'type' => 'MARKET',
            'quantity' => $quantity,
        ]);

        // Closing futures position
        $result = $binance_futures->trade()->postOrder([
            'symbol' => $symbol,
            'side' => 'BUY',
            'type' => 'MARKET',
            'positionSide' => 'Short',
            'closePosition' => true,
            'quantity' => $quantity,
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
