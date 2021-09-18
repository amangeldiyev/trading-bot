<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Lin\Binance\BinanceDelivery;
use Lin\Binance\BinanceFuture;

class BinanceController extends Controller
{
    protected $futuresApi;
    protected $deliveryApi;

    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

        $this->futuresApi = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        $this->deliveryApi = new BinanceDelivery(config('services.binance.api'), config('services.binance.secret'));
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
        $result = $this->futuresApi->market()->getPremiumIndex();

        $btc_funding = $this->fundingRate($result, 'BTCUSDT');

        $eth_funding = $this->fundingRate($result, 'ETHUSDT');

        $sorted = collect($result)->sortBy('lastFundingRate');

        $highest_funding = $sorted->last();

        $lowest_funding = $sorted->first();

        return view('funding-rates', compact('btc_funding', 'eth_funding', 'highest_funding', 'lowest_funding'));
    }

    public function open()
    {
        $symbol = 'ETHUSDT';
        $quarterly_symbol = 'ETHUSDT_210924';

        if (request()->isMethod('POST')) {
            $quantity = request('quantity');

            // Opening futures short position
            $this->marketOrder($symbol, $quantity, 'SELL', 'Short');

            // Opening futures quarterly long position
            $this->marketOrder($quarterly_symbol, $quantity, 'BUY', 'Long');
        }

        $difference = $this->priceDifference($quarterly_symbol, $symbol);

        return view('open', compact('difference'));
    }

    public function close()
    {
        $symbol = 'ETHUSDT';
        $quarterly_symbol = 'ETHUSDT_210924';

        if (request()->isMethod('POST')) {
            $quantity = request('quantity');

            // Closing perpetual short position
            $this->marketOrder($symbol, $quantity, 'BUY', 'Short');

            // Closing quarterly long position
            $this->marketOrder($quarterly_symbol, $quantity, 'SELL', 'Long');
        }

        $difference = $this->priceDifference($quarterly_symbol, $symbol);

        return view('close', compact('difference'));
    }

    public function coinM()
    {
        return view('coin-m');
    }

    public function openCoinM()
    {
        $binance_futures = new BinanceDelivery(config('services.binance.api'), config('services.binance.secret'));
        $symbol = 'XRPUSD_PERP';
        $quarterly_symbol = 'XRPUSD_211231';

        $result = $binance_futures->market()->getPremiumIndex();

        $first_price = Arr::first($result, function ($value) use ($quarterly_symbol) {
            return $value['symbol'] == $quarterly_symbol;
        })['markPrice'];

        $second_price = Arr::first($result, function ($value) use ($symbol) {
            return $value['symbol'] == $symbol;
        })['markPrice'];

        $difference = $first_price - $second_price;

        $quantity = 25;

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

        dd($difference);
    }

    public function settings(Request $request)
    {
        $price_difference_interval = Settings::firstOrNew([
            'name' => 'price_difference_interval'
        ]);

        if ($request->isMethod('POST')) {
            $price_difference_interval->value = $request->price_difference_interval;
            $price_difference_interval->save();
        }

        return view('settings', compact('price_difference_interval'));
    }

    private function fundingRate($result, $symbol)
    {
        return Arr::first($result, fn($value) => $value['symbol'] == $symbol)['lastFundingRate'];
    }

    private function priceDifference($first_symbol, $second_symbol)
    {
        $result = $this->futuresApi->market()->getPremiumIndex();

        $first_price = Arr::first($result, fn($value) => $value['symbol'] == $first_symbol)['markPrice'];

        $second_price = Arr::first($result, fn($value) => $value['symbol'] == $second_symbol)['markPrice'];

        return $first_price - $second_price;
    }

    private function marketOrder($symbol, $quantity, $side, $positionSide)
    {
        $this->futuresApi->trade()->postOrder([
            'symbol' => $symbol,
            'side' => $side,
            'type' => 'MARKET',
            'positionSide' => $positionSide,
            // 'closePosition' => true,
            'quantity' => $quantity,
        ]);
    }
}
