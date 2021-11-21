<?php

namespace App\Console\Commands;

use App\Models\PriceDifference as ModelsPriceDifference;
use App\Models\Settings;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
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

        $interval = Settings::whereName('price_difference_interval')->first()->value;

        if ($priceDifference) {
            if (abs($priceDifference->diff - $difference) >= $interval) {
                
                $days = Carbon::now()->diffInDays(new Carbon('2021-12-31'));

                $profit = $second_price * 0.0003 * ($days - 5) - $difference;

                Log::channel('slack_general')->info("ETH price difference is $difference. Estimated profit with 0.01% funding: $profit");

                $priceDifference->diff = $difference;
                $priceDifference->save();
            }
        } else {
            Log::channel('slack_general')->info("ETH price difference is $difference");

            ModelsPriceDifference::create([
                'first_symbol' => 'ETHUSDT_210924',
                'second_symbol' => 'ETHUSDT',
                'diff' => $difference
            ]);
        }
    }
}
