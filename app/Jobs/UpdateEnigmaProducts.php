<?php

namespace App\Jobs;

use App\Models\EnigmaProduct;
use App\Models\HuobiSymbol;
use App\Service\EnigmaSecurities;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Lin\Huobi\HuobiSpot;
use Illuminate\Support\Facades\Log;

class UpdateEnigmaProducts implements ShouldQueue
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
        $enigma = new EnigmaSecurities();

        $products = $enigma->getProducts();

        foreach ($products as $product) {
            EnigmaProduct::updateOrCreate(['product_name' => $product['product_name']], [
                'product_id' => $product['product_id'],
                'product_name' => $product['product_name'],
                'min_quantity' => $product['min_quantity'],
                'max_quantity' => $product['max_quantity'],
            ]);
        }
    }
}
