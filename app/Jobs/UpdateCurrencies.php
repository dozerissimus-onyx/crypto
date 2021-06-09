<?php

namespace App\Jobs;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Codenixsv\CoinGeckoApi\CoinGeckoClient;
use App\Models\Currency;
use App\Enums\CurrencyType;
use Illuminate\Support\Facades\Cache;

class UpdateCurrencies implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
    * The number of times the job may be attempted.
    *
    * @var int
    */
    public $tries = 100;

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
    * The unique ID of the job.
    *
    * @return string
    */
    public function uniqueId()
    {
        return 'update-currencies';
    }



    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        return $this->release(30);

        if ($timestamp = Cache::get('coin-gecko-limit')) {
            $this->release(
                $timestamp - time()
            );

            return;
        }
        try {
            $data = (new CoinGeckoClient())->coins()->getMarkets('usd');

            if (! is_array($data)) {
                Log::critical('Update Currencies Failed', [
                    'data' => $data,
                ]);

                return;
            }

            foreach ($data as $item) {
                $currency = Currency::whereType(CurrencyType::crypto)
                    ->whereCoingeckoId($item['id'])
                    ->first();

                if (! $currency) {
                    continue;
                }

                $currency->update([
                    'price' => $item['current_price'] ?? 0,
                    'ranking' => $item['market_cap_rank'] ?? 0,
                    '24h_change' => $item['price_change_percentage_24h'] ?? 0,
                    '24h_volume' => $item['total_volume'] ?? 0,
                    'circulating_supply' => $item['circulating_supply'] ?? 0,
                    'market_cap' => $item['market_cap'] ?? 0,
                ]);
            }
        } catch (RequestException $e) {
            if ($e->getCode() === 429) {
                $retryTime = $e->getResponse()->getHeader('Retry-After')[0] ??
                    $e->getResponse()->getHeader('X-RateLimit-Reset')[0] ?? 0;

                $secondsRemaining = strtotime($retryTime) ? now()->diffInSeconds($retryTime) : (int)$retryTime;

                Cache::put(
                    'coin-gecko-limit',
                    now()->addSeconds($secondsRemaining)->timestamp,
                    $secondsRemaining
                );

                $this->release($secondsRemaining);

                return;
            } else {
                Log::critical('Update Currencies Failed', [
                    'message' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @param \Exception $e
     */
    public function failed(\Exception $e) {
        Log::critical('FAILED', ['message' => $e->getMessage(), 'attempts' => $this->attempts()]);
    }
}
