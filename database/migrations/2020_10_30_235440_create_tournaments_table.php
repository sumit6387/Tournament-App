<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTournamentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id('tournament_id');
            $table->string('prize_pool');
            $table->string('winning'); // chicken dinner 
            $table->string('per_kill');
            $table->string('entry_fee');
            $table->string('type'); //solo , squad,Duo
            $table->string('map');
            $table->string('joined_user'); // how many user joined tournament
            $table->string('max_user_participated'); //max user participate in the tournaments
            $table->string('game_type'); //Faug,Pubg
            $table->boolean('completed')->default(false); //1 for completed oro for  not
            $table->string('tournament_type'); //public/private
            $table->string('created_by'); //user or admin
            $table->string('id'); //if tournament created by user then its user_id
            $table->string('user_id')->nullable(); //In particuller games login id
            $table->string('password')->nullable(); //In particuller games password
            $table->string('tournament_start_at'); //starting type of tournament
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
        Schema::dropIfExists('tournaments');
    }
}
