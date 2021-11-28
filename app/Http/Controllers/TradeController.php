<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Lin\Binance\Binance;
use Lin\Binance\BinanceDelivery;
use Lin\Binance\BinanceFuture;

class TradeController extends Controller
{
    public function __construct() {
        $this->middleware('auth');

        $this->spotApi = new Binance(config('services.binance.api'), config('services.binance.secret'));

        $this->futuresApi = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        $this->deliveryApi = new BinanceDelivery(config('services.binance.api'), config('services.binance.secret'));
    }

    public function index()
    {
        $response = $this->futuresApi->market()->getPremiumIndex();

        dd($response);
    }

    public function openSpotFutures()
    {
        $spot = 'DOTUSDT';
        $futures = 'DOTUSDT';
        $quantity = request('quantity') ?? 0.5;
        $lead = request('lead') ?? 0.07;
        $spread = request('spread') ?? 0.01;

        $result = $this->futuresApi->market()->getPremiumIndex();

        $mark_price = Arr::first($result, function ($value) use ($futures) {
            return $value['symbol'] == $futures;
        })['markPrice'];

        $price = round($mark_price, 2) - $lead;

        $this->spotApi->trade()->postOrder([
            'symbol' => $spot,
            'side' => 'BUY',
            'type' => 'LIMIT',
            'quantity' => $quantity,
            'price' => $price,
            'timeInForce' => 'GTC',
            
        ]);

        $this->futuresApi->trade()->postOrder([
            'symbol' => $futures,
            'side' => 'SELL',
            'type' => 'STOP',
            'positionSide' => 'Short',
            'price' => $price + $spread,
            'stopPrice' => $price,
            // 'closePosition' => true,
            'quantity' => $quantity,
        ]);
    }

    public function closeSpotFutures()
    {
        $spot = 'ETHUSDT';
        $futures = 'ETHUSDT';
        $quantity = request('quantity') ?? 0.01;

        $result = $this->futuresApi->market()->getPremiumIndex();

        $mark_price = Arr::first($result, function ($value) use ($futures) {
            return $value['symbol'] == $futures;
        })['markPrice'];

        $price = round($mark_price, 2) + 5.5;

        $this->spotApi->trade()->postOrder([
            'symbol' => $spot,
            'side' => 'SELL',
            'type' => 'LIMIT',
            'quantity' => $quantity,
            'price' => $price,
            'timeInForce' => 'GTC',
        ]);

        $this->futuresApi->trade()->postOrder([
            'symbol' => $futures,
            'side' => 'BUY',
            'type' => 'STOP',
            'positionSide' => 'Short',
            'price' => $price,
            'stopPrice' => $price + 0.5,
            // 'closePosition' => true,
            'quantity' => $quantity,
        ]);
    }

    public function reinvestFutures()
    {
        $symbol = 'DOTUSDT';
        $quantity = request('quantity') ?? 0.5;
        $lead = request('lead') ?? 0.07;
        $spread = request('spread') ?? 0.02;

        $result = $this->futuresApi->market()->getPremiumIndex();

        $price = Arr::first($result, function ($value) use ($symbol) {
            return $value['symbol'] == $symbol;
        })['markPrice'];

        $price = round($price, 2) + $lead;

        $this->futuresApi->trade()->postOrder([
            'symbol' => $symbol,
            'side' => 'SELL',
            'type' => 'LIMIT',
            'positionSide' => 'Short',
            'price' => $price,
            'quantity' => $quantity,
            'timeInForce' => 'GTC',
        ]);

        $this->futuresApi->trade()->postOrder([
            'symbol' => $symbol,
            'side' => 'BUY',
            'type' => 'STOP',
            'positionSide' => 'Short',
            'price' => $price - $spread,
            'stopPrice' => $price,
            'quantity' => $quantity,
        ]);

        return;
    }

    public function reinvestCoin()
    {
        $symbol = 'DOTUSD_PERP';
        $quantity = request('quantity') ?? 4;
        $lead = request('lead') ?? 0.07;
        $spread = request('spread') ?? 0.02;

        $result = $this->deliveryApi->market()->getPremiumIndex();

        $price = Arr::first($result, function ($value) use ($symbol) {
            return $value['symbol'] == $symbol;
        })['markPrice'];

        $price = round($price, 2) + $lead;

        $this->deliveryApi->trade()->postOrder([
            'symbol' => $symbol,
            'side' => 'SELL',
            'type' => 'LIMIT',
            'positionSide' => 'Short',
            'price' => $price,
            'quantity' => $quantity,
            'timeInForce' => 'GTC',
        ]);

        $this->deliveryApi->trade()->postOrder([
            'symbol' => $symbol,
            'side' => 'BUY',
            'type' => 'STOP',
            'positionSide' => 'Short',
            'price' => $price - $spread,
            'stopPrice' => $price,
            'quantity' => $quantity,
        ]);

        return;

        // $result = $this->deliveryApi->market()->getPremiumIndex();

        // $price = Arr::first($result, function ($value) use ($symbol) {
        //     return $value['symbol'] == $symbol;
        // })['markPrice'];

        // $downPrice = round($price, 2) - 0.07;

        // $this->deliveryApi->trade()->postOrder([
        //     'symbol' => $symbol,
        //     'side' => 'SELL',
        //     'type' => 'STOP',
        //     'positionSide' => 'Short',
        //     'price' => $downPrice + 0.01,
        //     'stopPrice' => $downPrice,
        //     'quantity' => $quantity,
        //     'timeInForce' => 'GTC',
        // ]);

        // $this->deliveryApi->trade()->postOrder([
        //     'symbol' => $symbol,
        //     'side' => 'BUY',
        //     'type' => 'LIMIT',
        //     'positionSide' => 'Short',
        //     'price' => $downPrice,
        //     'quantity' => $quantity,
        //     'timeInForce' => 'GTC',
        // ]);

    }

    public function wash()
    {
        $symbol = 'ETHBUSD';
        $quantity = request('quantity') ?? 0.01;
        $lead = request('lead') ?? 5;
        $spread = request('spread') ?? 0.2;

        $result = $this->futuresApi->market()->getPremiumIndex();

        $mark_price = Arr::first($result, function ($value) use ($symbol) {
            return $value['symbol'] == $symbol;
        })['markPrice'];

        $price = round($mark_price, 2) + $lead;

        // open long
        // $this->futuresApi->trade()->postOrder([
        //     'symbol' => $symbol,
        //     'side' => 'BUY',
        //     'type' => 'STOP',
        //     'positionSide' => 'Long',
        //     'price' => $price - 0.2,
        //     'stopPrice' => $price,
        //     'quantity' => $quantity,
        // ]);

        // open short
        // $this->futuresApi->trade()->postOrder([
        //     'symbol' => $symbol,
        //     'side' => 'SELL',
        //     'type' => 'LIMIT',
        //     'positionSide' => 'Short',
        //     'price' => $price,
        //     'quantity' => $quantity,
        //     'timeInForce' => 'GTC',
        // ]);

        $this->futuresApi->trade()->postOrder([
            'symbol' => $symbol,
            'side' => 'SELL',
            'type' => 'LIMIT',
            'positionSide' => 'Short',
            'price' => $price,
            'quantity' => $quantity,
            'timeInForce' => 'GTC',
        ]);

        $this->futuresApi->trade()->postOrder([
            'symbol' => $symbol,
            'side' => 'BUY',
            'type' => 'STOP',
            'positionSide' => 'Short',
            'price' => $price - $spread,
            'stopPrice' => $price,
            'quantity' => $quantity,
        ]);
    }
}
