<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use MockingMagician\CoinbaseProSdk\CoinbaseFacade;

class GetCoinBaseFeeEstimates implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //        $baseUrl = 'https://api.pro.coinbase.com';
        $baseUrl = 'https://api.exchange.coinbase.com';

        $coinBase = CoinbaseFacade::createDefaultCoinbaseApi(
            $baseUrl,
            config('api.coinBasePro.key'),
            config('api.coinBasePro.secret'),
            config('api.coinBasePro.passphrase')
        );

        $accounts = Account::all();

        foreach ($accounts as $account) {
            $account->coinbase_fee = $coinBase->withdrawals()->getFeeEstimate($account->currency_code, $account->deposit_address);
            $account->save();

            usleep(100000);
        }
    }
}
