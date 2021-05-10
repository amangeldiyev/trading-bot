<?php

namespace App\Console\Commands;

use App\Models\Position;
use Illuminate\Console\Command;
use Lin\Binance\BinanceFuture;

class EmaIndicatorStrategy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'strategy:ema-indicator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ema indicator strategy';

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
            'symbol' => 'BTCUSDT',
            'interval' =>  '5m',
            'limit' => 150
        ]);

        $numbers = [];

        foreach ($result as $candle) {
            $numbers[] = $candle[1];
        }
        
        $ema10_array = $this->exponentialMovingAverage($numbers, 10);
        $ema20_array = $this->exponentialMovingAverage($numbers, 20);
        $ema30_array = $this->exponentialMovingAverage($numbers, 30);

        $ema10 = end($ema10_array);
        $ema20 = end($ema20_array);
        $ema30 = end($ema30_array);

        $mark_price = $binance->market()->getDepth([
            'symbol'=>'BTCUSDT',
            'limit'=>'5',
        ])['asks'][0][0];

        $position = Position::first();

        if ($ema10 > $ema20 && $ema20 > $ema30 && !$position) {

            info("Ordering with price $mark_price");

            Position::create([
                'symbol' => 'BTCUSDT',
                'amount' => '0.003',
                'entry_price' => $mark_price,
                'mark_price' => $mark_price,
                'unrealized_pnl' => 0,
                'liquidation_price' => 0,
                'leverage' => 5,
                'position_side' => 1,
            ]);
        } else if ($ema10 < $ema20 && $ema20 < $ema30 && $position) {
            info("Closing position on $mark_price");

            $position->delete();
        }
    }

    /**
     * EMA Calculator
     * 
     * @param array array of numbers
     * @param int number of days
     * @return array
     */
    public static function exponentialMovingAverage(array $numbers, int $n): array
    {
        $m   = count($numbers);
        $α   = 2 / ($n + 1);
        $EMA = [];

        // Start off by seeding with the first data point
        $EMA[] = (int)$numbers[0];

        // Each day after: EMAtoday = α⋅xtoday + (1-α)EMAyesterday
        for ($i = 1; $i < $m; $i++) {
            $EMA[] = ($α * $numbers[$i]) + ((1 - $α) * $EMA[$i - 1]);
        }

        return $EMA;
    }
}
