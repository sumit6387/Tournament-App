<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLudoTournamentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ludo_tournament', function (Blueprint $table) {
            $table->id();
            $table->string("ludo_id"); //ludo_ (random 8 alphaNumeric value)
            $table->json("user1"); //who created tournament,ludo username in json format
            $table->json("user2")->nullable(); //who joined tournament ,ludo username in json format
            $table->string("winning"); //after cut the comission
            $table->string("entry_fee"); //entry fee
            $table->string("game"); //Ludo,snake & ladders
            $table->string("room_id"); //ludo's room ID
            $table->string("completed")->default(false);
            $table->string("cancel")->default(false);
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
        Schema::dropIfExists('ludo_tournament');
    }
}
