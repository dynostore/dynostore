<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServerToChunks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chunks', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('server_id')->nullable(false)->after('keyfile');
            $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chunks', function (Blueprint $table) {
            //
            $table->removeColumn("server_id");
        });
    }
}
