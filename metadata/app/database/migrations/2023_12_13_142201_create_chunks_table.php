<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChunksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chunks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('keyfile')->index()->nullable(false);
            $table->string('name');
            $table->unsignedDouble('size');
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
        Schema::dropIfExists('chunks');
    }
}
