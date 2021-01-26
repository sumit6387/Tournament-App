<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLudoTournamentResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ludoTournamentResult', function (Blueprint $table) {
            $table->id();
            $table->string('tournament_id');
            $table->string('winner')->nullable();
            $table->string('img1')->nullable();
            $table->string('img2')->nullable();
            $table->string('looser1')->nullable();
            $table->string('looser2')->nullable();
            $table->string('status')->default(false); //0 means in review or one user updated result and 1 means reviw over and money transfer in the account
            $table->string('error1')->nullable();
            $table->string('error2')->nullable();
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
        Schema::dropIfExists('ludoTournamentResult');
    }
}
