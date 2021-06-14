<?php

namespace App\Jobs;

use App\Models\HuobiSymbol;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Lin\Huobi\HuobiSpot;
use Illuminate\Support\Facades\Log;

class UpdateHuobiSymbols implements ShouldQueue
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
        $huobiSpot = new HuobiSpot(env('HUOBI_KEY', env('HUOBI_SECRET')));

        $symbols = $huobiSpot->common()->getSymbols();

        foreach ($symbols['data'] as $symbol) {
            DB::table('huobi_symbols')->updateOrInsert(['symbol' => $symbol['symbol']], [
                'base_currency' => $symbol['base-currency'],
                'quote_currency' => $symbol['quote-currency'],
                'min_order_amount' => $symbol['limit-order-min-order-amt'],
                'max_order_amount' => $symbol['limit-order-max-order-amt'],
                'updated_at' => now()
            ]);
        }
    }
}
