<?php

namespace App\Jobs;

use GuzzleHttp\Exception\RequestException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Codenixsv\CoinGeckoApi\CoinGeckoClient;
use App\Models\Currency;
use App\Models\CurrencyChart;
use App\Enums\CurrencyType;

class UpdateCurrencyCharts implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var int */
    protected $range;

    /**
     * @var int
     */
    public $tries = 0;

    /**
     * Create a new job instance.
     *
     * @param int $range
     * @return void
     */
    public function __construct(int $range)
    {
        $this->range = $range;
    }

    /**
    * The unique ID of the job.
    *
    * @return string
    */
    public function uniqueId()
    {
        return 'get-coin-gecko-data';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($timestamp = Cache::get('coin-gecko-limit')) {
            return $this->release(
                $timestamp - time()
            );
        }

        $coingecko = new CoinGeckoClient();
        $ranges = [
            CurrencyChart::RANGE_DAY => '1',
            CurrencyChart::RANGE_WEEK => '7',
            CurrencyChart::RANGE_MONTH => '30',
            CurrencyChart::RANGE_YEAR => '365',
            CurrencyChart::RANGE_ALL => 'max',
        ];
        $range = $ranges[$this->range];

        $currencies = Currency::with(['charts' => function($query) use ($range) {
            $query->where('range', $range);
            $query->where('updated_at', '<=', now()->subMinutes(5));
        }])->whereType(CurrencyType::crypto)
            ->where('show_on_prices', true)
            ->whereNotNull('coingecko_id')
            ->get();

        foreach ($currencies as $currency) {
            if (! count($currency->charts)) {
                continue;
            }

            try {
                $data = $coingecko->coins()->getMarketChart($currency->coingecko_id, 'usd', $range);

                if (! is_array($data) || ! isset($data['prices'])) {
                    Log::critical('Update Currency Charts Failed', [
                        'id' => $currency->coingecko_id,
                        'message' => $data,
                    ]);
                    continue;
                }
                $currency->charts()->updateOrCreate(
                    [
                        'currency_id' => $currency->id,
                        'range' => $this->range,
                    ],
                    ['stats' => json_encode($data['prices'])]
                );
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

                    return $this->release($secondsRemaining);
                } else {
                    Log::critical('Update Currency Charts Failed', [
                        'id' => $currency->coingecko_id,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    /**
     * @return \Illuminate\Support\Carbon
     */
    public function retryUntil()
    {
        return now()->addHours(12);
    }
}
