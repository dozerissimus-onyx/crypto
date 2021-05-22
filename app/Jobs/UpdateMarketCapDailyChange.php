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
use App\Models\NovaSetting;

class UpdateMarketCapDailyChange implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var int
     */
    public $backoff = 60;

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
        return 'update-market-cap-daily-change';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $data = (new CoinGeckoClient())->globals()->getGlobal();

            if (! is_array($data) || ! isset($data['data']['market_cap_change_percentage_24h_usd'])) {
                Log::critical('Update Market Cap Daily Change Failed', ['message' => 'Wrong Response']);

                $this->delete();

                return;
            }

            $value = $data['data']['market_cap_change_percentage_24h_usd'];
            $setting = NovaSetting::whereKey('market_cap_daily_change')
                ->first();

            if (! $setting) {
                Log::critical('Update Market Cap Daily Change Failed', ['message' => 'Model Not Found']);

                $this->delete();

                return;
            }

            $setting->update([
                'value' => $value,
            ]);
        } catch (\Throwable $e) {
            report($e);

            Log::critical('Update Market Cap Daily Change Failed', ['message' => $e->getMessage()]);

            $this->delete();
        }
    }
}
