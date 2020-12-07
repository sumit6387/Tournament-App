<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('reciept_id');
            $table->integer('amount');
            $table->string('description')->nullable();
            $table->string('payment_id'); //this is order id of requesting payment
            $table->string('action'); //action field is for like debit credit  
            $table->string('razorpay_id')->nullable(); //that is the razorpay id
            $table->boolean('payment_done')->default(false);
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
        Schema::dropIfExists('transactions');
    }
}
