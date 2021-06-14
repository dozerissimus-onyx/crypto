<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHuobiSymbolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('huobi_symbols', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 20);
            $table->string('quote_currency', 20);
            $table->string('symbol', 20);
            $table->float('min_order_amount');
            $table->float('max_order_amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('huobi_symbols');
    }
}
