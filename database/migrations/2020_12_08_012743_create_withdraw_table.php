<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('withdraw', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('name')->nullable();
            $table->string('transaction_id')->unique();
            $table->string('ifsc_code')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('acount_no')->nullable();
            $table->string('paytm_no')->nullable();
            $table->string('upi_id')->nullable();
            $table->string('mode');
            $table->boolean('completed')->default(0);
            $table->integer('amount');
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
        Schema::dropIfExists('withdraw');
    }
}
