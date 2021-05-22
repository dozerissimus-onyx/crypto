<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

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
        return 'update-currency-charts';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
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
            $query->where('updated_at', '<=', date('Y-m-d H:i:s', strtotime('-5 minutes')));
        }])->whereType(CurrencyType::crypto)
            ->where('show_on_prices', true)
            ->whereNotNull('coingecko_id')
            ->get();

        $i = 0;
        while ($currency = $currencies[$i] ?? null) {
            if (!count($currency->charts)) {
                $i++;
                continue;
            }
            try {
                $data = $coingecko->coins()->getMarketChart($currency->coingecko_id, 'usd', $range);

                if (! is_array($data) || ! isset($data['prices'])) {
                    Log::critical('Update Currency Charts Failed', [
                        'id' => $currency->coingecko_id,
                        'message' => $data,
                    ]);
                    $i++;
                    continue;
                }
                $currency->charts()->updateOrCreate(
                    [
                        'currency_id' => $currency->id,
                        'range' => $this->range,
                    ],
                    ['stats' => json_encode($data['prices'])]
                );
                $i++;
            } catch (\Throwable $e) {
                if ($e->getCode() == 429) {
                    sleep(10);
                    continue;
                }
                Log::critical('Update Currency Charts Failed', [
                    'id' => $currency->coingecko_id,
                    'message' => $e->getMessage(),
                ]);

                $i++;
            }
        }
    }
}
