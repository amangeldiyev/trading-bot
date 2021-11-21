<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Lin\Binance\Binance;
use Lin\Binance\BinanceDelivery;
use Lin\Binance\BinanceFuture;

class MarketDataController extends Controller
{
    protected $spotApi;
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

        $this->spotApi = new Binance(config('services.binance.api'), config('services.binance.secret'));

        $this->futuresApi = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        $this->deliveryApi = new BinanceDelivery(config('services.binance.api'), config('services.binance.secret'));
    }

    public function priceDifference()
    {
        $data = $this->deliveryApi->market()->getPremiumIndex();

        $symbols = ['BTCUSD', 'ETHUSD', 'ADAUSD', 'XRPUSD', 'DOTUSD', 'BNBUSD', 'LINKUSD', 'LTCUSD', 'BCHUSD'];

        foreach ($symbols as $symbol) {
            $first_price = Arr::first($data, fn ($value) => $value['symbol'] == $symbol.'_PERP')['markPrice'];

            $second_price = Arr::first($data, fn ($value) => $value['symbol'] == $symbol.'_211231')['markPrice'];

            dump("$symbol - ".round(($second_price - $first_price) / $first_price * 100, 3) . '%');
        }
    }
}
