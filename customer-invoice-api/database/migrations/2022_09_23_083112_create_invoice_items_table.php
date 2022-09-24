<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->string('invoice_id',8);
            $table->foreign('invoice_id')->on('invoices')->references('invoice_id')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->integer('item_id');
            $table->string('vin');
            $table->string('brend');
            $table->string('marka');
            $table->integer('sayi');
            $table->integer('qiymeti');
            $table->integer('cemi');
            $table->integer('status');
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
        Schema::dropIfExists('invoice_items');
    }
};
