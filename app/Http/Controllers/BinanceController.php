<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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

    public function totalFundingRate(Request $request)
    {
        $symbol = $request->symbol;
        $start = $request->start;
        $end = $request->end;
        $total = 0;

        if ($request->isMethod('POST')) {
            $binance = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

            $startTime = Carbon::createFromFormat('Y-m-d', $request->start)->timestamp;
            $endTime = Carbon::createFromFormat('Y-m-d', $request->end)->timestamp;

            $result = $binance->market()->getFundingRate([
                'symbol' => $request->symbol,
                'startTime' => $startTime . '000',  // 1626813052 1627228800000
                'endTime' => $endTime . '000',
            ]);

            foreach ($result as $rates) {
                $total += $rates['fundingRate'];
            }
        }

        return view('total-funding-rate', compact('symbol', 'start', 'end', 'total'));
    }

    public function fundingRates()
    {
        $binance = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        $result = $binance->market()->getPremiumIndex();

        $btc_funding = Arr::first($result, function ($value) {
            return $value['symbol'] == 'BTCUSDT';
        })['lastFundingRate'];

        $eth_funding = Arr::first($result, function ($value) {
            return $value['symbol'] == 'ETHUSDT';
        })['lastFundingRate'];

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

        return view('funding-rates', compact('btc_funding', 'eth_funding', 'highest_funding', 'lowest_funding'));
    }

    public function open()
    {
        $binance_futures = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        $result = $binance_futures->market()->getPremiumIndex();

        $first_price = Arr::first($result, function ($value) {
            return $value['symbol'] == 'ETHUSDT_210924';
        })['markPrice'];

        $second_price = Arr::first($result, function ($value) {
            return $value['symbol'] == 'ETHUSDT';
        })['markPrice'];

        $difference = $first_price - $second_price;

        if (request()->isMethod('POST')) {
            $symbol = 'ETHUSDT';
            $quarterly_symbol = 'ETHUSDT_210924';
            $quantity = request('quantity');

            // Opening futures short position
            $result = $binance_futures->trade()->postOrder([
                'symbol' => $symbol,
                'side' => 'SELL',
                'type' => 'MARKET',
                'positionSide' => 'Short',
                'quantity' => $quantity,
            ]);

            // Opening futures quarterly long position
            $result = $binance_futures->trade()->postOrder([
                'symbol' => $quarterly_symbol,
                'side' => 'BUY',
                'type' => 'MARKET',
                'positionSide' => 'Long',
                'quantity' => $quantity,
            ]);
        }

        return view('open', compact('difference'));
    }

    public function close()
    {
        $binance_futures = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        $result = $binance_futures->market()->getPremiumIndex();

        $first_price = Arr::first($result, function ($value) {
            return $value['symbol'] == 'ETHUSDT_210924';
        })['markPrice'];

        $second_price = Arr::first($result, function ($value) {
            return $value['symbol'] == 'ETHUSDT';
        })['markPrice'];

        $difference = $first_price - $second_price;

        if (request()->isMethod('POST')) {
            $symbol = 'ETHUSDT';
            $quarterly_symbol = 'ETHUSDT_210924';
            $quantity = request('quantity');

            // Closing perpetual short position
            $result = $binance_futures->trade()->postOrder([
                'symbol' => $symbol,
                'side' => 'BUY',
                'type' => 'MARKET',
                'positionSide' => 'Short',
                // 'closePosition' => true,
                'quantity' => $quantity,
            ]);

            // Closing quarterly long position
            $result = $binance_futures->trade()->postOrder([
                'symbol' => $quarterly_symbol,
                'side' => 'SELL',
                'type' => 'MARKET',
                'positionSide' => 'Long',
                // 'closePosition' => true,
                'quantity' => $quantity,
            ]);
        }

        return view('close', compact('difference'));
    }
}
