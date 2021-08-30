<?php

namespace App\Console\Commands;

use App\Models\Account;
use Illuminate\Console\Command;
use Lin\Binance\BinanceFuture;

class TrackPnl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'account:track-pnl';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify user about unrealized pnl change';

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

        $result = $binance->user()->getAccount();

        $account = Account::first();

        $unrealized_pnl = $result['totalUnrealizedProfit'];
        
        if($account) {

            $pnl_diff = $unrealized_pnl - $account->unrealized_pnl;

            if (abs($pnl_diff) > 20) {
                info("Unrealized pnl change: $pnl_diff");
                $account->update([
                    'wallet_balance' => $result['totalWalletBalance'],
                    'unrealized_pnl' => $unrealized_pnl
                ]);
            }

        } else {
            Account::create([
                'wallet_balance' => $result['totalWalletBalance'],
                'unrealized_pnl' => $unrealized_pnl
            ]);
        }
    }
}
