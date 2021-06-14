<?php

namespace App\Jobs;

use App\Models\Currency;
use App\Models\HuobiOrder;
use Lin\Huobi\HuobiSpot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GetHuobiOrderStatus implements ShouldQueue
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
        $huobiSpot = new HuobiSpot(env('HUOBI_KEY', env('HUOBI_SECRET')));

        $orders = HuobiOrder::whereNotIn('status', [HuobiOrder::STATUS_SUBMITTED, HuobiOrder::STATUS_CLOSED])->get();

        foreach ($orders as $order) {
            $response = $huobiSpot->order()->get([
                'order-id' => $order->order_id
            ]);

            $order->status = $response['data']['state'];

            if (in_array($order->status, [HuobiOrder::STATUS_CANCELED, HuobiOrder::STATUS_SUBMITTED])) {
                if ($order->status === HuobiOrder::STATUS_CANCELED) {
                    $marketData = $huobiSpot->market()->getDetailMerged(['symbol' => $order->symbol]);

                    if ($marketData['tick']['close'] > $order->quote - $order->quote * 0.05) {
                        $orderId = $huobiSpot->order()->postPlace([
                            'account-id' => env('HUOBI_ACCOUNT_ID'),
                            'symbol' => $order->symbol,
                            'type' => $order->type,
                            'amount' => $order->amount,
                        ]);
                        $order->orderId = $orderId;
                        $order->status = HuobiOrder::STATUS_CREATED;
                        $order->save();

                        continue;
                    }
                    $currencyId = $order->sell_currency_id;
                    $amount = $order->amount;
                } else {
                    $currencyId = $order->buy_currency_id;
                    $amount = $response['data']['field-cash-amount'];
                }

                $currency = Currency::find($currencyId);
                $account = Account::where(['id' => $order->user_id, 'currency_id' => $currencyId]);

                $transferId = $huobiSpot->wallet()->postWithdrawApiCreate([
                    'address' => $account->deposit_address,
                    'currency' => $currency->code,
                    'amount' => $amount,
                    'addr-tag' => $account->deposit_address_tag,
                    'fee' => 0
                ]);

                //some actions with transferId
            }

            $order->save();
        }
    }
}
