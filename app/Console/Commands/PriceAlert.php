<?php

namespace App\Console\Commands;

use App\Models\PriceAlert as ModelsPriceAlert;
use Illuminate\Console\Command;
use Lin\Binance\BinanceFuture;

class PriceAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alert:price {symbol} {interval} {range}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alerts about price change';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $binance = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        $result = $binance->market()->getKlines([
            'symbol' => $this->argument('symbol'),
            'interval' =>  $this->argument('interval'),
            'limit' => 10
        ]);

        $close_price = array_slice($result, -2, 1)[0][4];

        $price_alert = ModelsPriceAlert::where('symbol', $this->argument('symbol'))
                                ->where('interval', $this->argument('interval'))
                                ->first();

        if ($price_alert) {

            if (($price_alert->price - $close_price) > $this->argument('range')) {
                info("Price closed at $close_price");

                $price_alert->price = $close_price;
            } else if ($close_price > $price_alert->price) {
                $price_alert->price = $close_price;
            }

            $price_alert->save();

        } else {
            ModelsPriceAlert::create([
                'symbol' => $this->argument('symbol'),
                'interval' => $this->argument('interval'),
                'price' => $close_price,
            ]);
        }
    }
}
