<?php

namespace App\Console\Commands;

use App\Models\PriceDifference as ModelsPriceDifference;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Lin\Binance\BinanceFuture;

class PriceDifference extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alert:price-difference {first_symbol} {second_symbol} {difference}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Alert price difference of two assets.';

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
        $first_symbol = $this->argument('first_symbol');
        $second_symbol = $this->argument('second_symbol');

        $binance = new BinanceFuture(config('services.binance.api'), config('services.binance.secret'));

        $result = $binance->market()->getPremiumIndex();

        $first_price = Arr::first($result, function ($value) use($first_symbol){
            return $value['symbol'] == $first_symbol;
        })['markPrice'];

        $second_price = Arr::first($result, function ($value) use($second_symbol){
            return $value['symbol'] == $second_symbol;
        })['markPrice'];


        $difference = $first_price - $second_price;

        $priceDifference = ModelsPriceDifference::where('first_symbol', 'ETHUSDT_210924')
                                        ->where('second_symbol', 'ETHUSDT')
                                        ->first();

        if ($priceDifference) {
            if ($priceDifference->diff - $difference >= $this->argument('difference')) {
                info("New price difference is $difference");

                $priceDifference->diff = $difference;
                $priceDifference->save();
            }
        } else {
            info("Price difference is $difference");

            ModelsPriceDifference::create([
                'first_symbol' => 'ETHUSDT_210924',
                'second_symbol' => 'ETHUSDT',
                'diff' => $difference
            ]);
        }
    }
}
