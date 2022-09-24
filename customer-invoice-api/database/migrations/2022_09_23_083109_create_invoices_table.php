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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->integer('customer_id');
            $table->string('invoice_id',8)->unique();
            $table->string('status'); //Billed, Paid, Void
            $table->dateTime('invoice_date');
            $table->dateTime('invoice_due_date');
            $table->integer('subTotal');
            $table->integer('percentage');
            $table->integer('percentageAmount');
            $table->integer('totalAfterpercentage');
            $table->integer('amountPaid');
            $table->integer('amountDue');
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
        Schema::dropIfExists('invoices');
    }
};
