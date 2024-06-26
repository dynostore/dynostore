<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAbekeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('abekeys', function (Blueprint $table) {
            $table->id();
            $table->string('keyfile')->index()->nullable(false);
            $table->string('url');
            $table->timestamps();

            $table->foreign('keyfile')->references('keyfile')->on('files')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('abekeys');
    }
}
