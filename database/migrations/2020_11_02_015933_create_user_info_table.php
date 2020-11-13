<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_info', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('profile_image')->nullable();
            $table->string('gender')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('refferal_code'); 
            $table->string('ref_by')->nullable();
            $table->string('first_time_payment')->default('0');
            $table->string('withdrawal_amount')->default('0');
            $table->string('wallet_amount')->default('0');
            $table->string('ptr_reward')->default('0');
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
        Schema::dropIfExists('user_info');
    }
}
