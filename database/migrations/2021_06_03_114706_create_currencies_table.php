<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurrenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('coingecko_id');
            $table->string('price')->default(0);
            $table->string('ranking')->default(0);
            $table->string('24h_change')->default(0);
            $table->string('24h_volume')->default(0);
            $table->string('circulating_supply')->default(0);
            $table->string('market_cap')->default(0);
            $table->string('show_on_prices')->default(0);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('currencies');
    }
}
