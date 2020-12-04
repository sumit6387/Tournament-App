<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppVersionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_vesrions', function (Blueprint $table) {
            $table->id();
            $table->string('version'); //0.0.1 , 0.0.2
            $table->string('short_version');  //v1,v2,v3
            $table->string('app_link');  //v1,v2,v3
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
        Schema::dropIfExists('app_vesrions');
    }
}
