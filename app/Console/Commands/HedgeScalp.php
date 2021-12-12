<?php

namespace App\Console\Commands;

use App\Models\Settings;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Lin\Binance\BinanceFuture;

class HedgeScalp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'strategy:hedge-scalp {symbol} {quantity} {range} {spread} {reactivationDiff}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scalp hedge mode';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->api = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $is_enabled = Settings::where('name', 'hedge_scalp_enabled')->where('value', 1)->first();
        
        if (!$is_enabled) {
            info('disabled');
            return 0;
        }

        $symbol = $this->argument('symbol');
        $quantity = $this->argument('quantity');
        $range = $this->argument('range');
        $spread = $this->argument('spread');
        $reactivationDiff = $this->argument('reactivationDiff');

        // Get orders
        $orders = $this->api->user()->getOpenOrders([
            'symbol' => $symbol,
            'recvWindow' => 10000
        ]);

        if (empty($orders)) {
            $this->createOrders($symbol, $range, $spread, $quantity);
        } elseif (count($orders) == 2) {

            // Mark Price
            $price = $this->markPrice($symbol);

            // check order price and mark price
            if ($orders[0]['price'] - $price > $reactivationDiff) {
                // cancel orders
                info('canceling orders');
                foreach ($orders as $order) {
                    $result = $this->api->trade()->deleteOrder([
                        'symbol' => $symbol,
                        'orderId' => $order['orderId']
                    ]);
                }

                $this->createOrders($symbol, $range, $spread, $quantity);
            }
        } else {
            $this->info('do nothing');
        }
    }

    private function createOrders($symbol, $range, $spread, $quantity)
    {
        $price = $this->markPrice($symbol);

        $price = round($price, 1) + $range;

        info('creating orders');

        $this->api->trade()->postOrder([
            'symbol' => $symbol,
            'side' => 'SELL',
            'type' => 'LIMIT',
            'positionSide' => 'Short',
            'price' => $price,
            'quantity' => $quantity,
            'timeInForce' => 'GTC',
            'recvWindow' => 10000
        ]);

        $this->api->trade()->postOrder([
            'symbol' => $symbol,
            'side' => 'BUY',
            'type' => 'STOP',
            'positionSide' => 'Short',
            'price' => $price - $spread,
            'stopPrice' => $price,
            'quantity' => $quantity,
            'recvWindow' => 10000
        ]);
    }

    private function markPrice($symbol)
    {
        $result = $this->api->market()->getPremiumIndex();

        return Arr::first($result, fn($value) => $value['symbol'] == $symbol)['markPrice'];
    }
}
