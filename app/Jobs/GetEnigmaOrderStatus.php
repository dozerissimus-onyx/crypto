<?php

namespace App\Jobs;

use App\Models\Currency;
use App\Models\EnigmaOrder;
use App\Models\HuobiOrder;
use App\Service\EnigmaSecurities;
use Lin\Huobi\HuobiSpot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetEnigmaOrderStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $enigma = new EnigmaSecurities();

        $orders = EnigmaOrder::whereNotIn('status', [EnigmaOrder::STATUS_CLOSED])->get();

        foreach ($orders as $order) {
            $response = $enigma->getTrade([
                'product_id' => $order->product_id,
                'trade_id' => $order->order_id
            ]);

            if (!$order->status = $response['items'][0]['status'] ?? null) {
                continue;
            }

            if ($order->status === EnigmaOrder::STATUS_VALIDATED) {
                $fee = 0;
                $currencyCodes = explode('-', $order->product_name);

                if ($order->side === 'buy') {
                    // Deposit::create
                    // $currency = $currencyCodes[0];
                    // $userId = $order->user_id;
                    // $amount = $order->quantity - $fee;
                } elseif ($order->side === 'sell') {
                    // Payout
                }


                $order->status = EnigmaOrder::STATUS_CLOSED;
            }

            $order->save();
        }
    }
}
