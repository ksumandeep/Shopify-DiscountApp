<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carts_table', function (Blueprint $table) {
            $table->id();
            $table->string('store_name');
            $table->string('discount_code');
            $table->string('discount_amount');
            $table->string('cart_id');
            $table->string('items');
            $table->string('created_at');
            $table->string('active');
            $table->string('otp');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carts_table');
    }
}
