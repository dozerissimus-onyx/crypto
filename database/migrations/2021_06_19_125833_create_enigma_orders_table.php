<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEnigmaOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('enigma_orders', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->string('type');
            $table->integer('product_id');
            $table->string('product_name');
            $table->integer('user_id');
            $table->string('message');
            $table->string('side');
            $table->double('quantity');
            $table->double('price');
            $table->double('nominal');
            $table->string('status');
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
        Schema::dropIfExists('enigma_orders');
    }
}
