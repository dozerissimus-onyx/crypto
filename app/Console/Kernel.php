<?php

namespace App\Console;

use App\Jobs\GetCoinBaseFeeEstimates;
use App\Jobs\GetHuobiOrderStatus;
use App\Jobs\UpdateEnigmaProducts;
use App\Jobs\UpdateHuobiSymbols;
use App\Models\CurrencyChart;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\UpdateCurrencies;
use App\Jobs\UpdateMarketCapDailyChange;
use App\Jobs\UpdateCurrencyCharts;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->job(new GetHuobiOrderStatus)
            ->everyMinute()
            ->withoutOverlapping();
        $schedule->job(new UpdateHuobiSymbols)
            ->daily()
            ->withoutOverlapping();
        $schedule->job(new UpdateEnigmaProducts)
            ->everyMinute()
            ->withoutOverlapping();
        $schedule->job(new GetCoinBaseFeeEstimates)
            ->everyMinute()
            ->withoutOverlapping();
//        $schedule->job(new UpdateCurrencies)
//            ->everyMinute()
//            ->withoutOverlapping();
//        $schedule->job(new UpdateMarketCapDailyChange)
//            ->everyFiveMinutes()
//            ->withoutOverlapping();
//        $schedule->job(new UpdateCurrencyCharts(CurrencyChart::RANGE_DAY))
//            ->everyMinute()
//            ->withoutOverlapping();
//        $schedule->job(new UpdateCurrencyCharts(CurrencyChart::RANGE_WEEK))
//            ->daily()
//            ->withoutOverlapping();
//        $schedule->job(new UpdateCurrencyCharts(CurrencyChart::RANGE_MONTH))
//            ->daily()
//            ->withoutOverlapping();
//        $schedule->job(new UpdateCurrencyCharts(CurrencyChart::RANGE_YEAR))
//            ->daily()
//            ->withoutOverlapping();
//        $schedule->job(new UpdateCurrencyCharts(CurrencyChart::RANGE_ALL))
//            ->daily()
//            ->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
