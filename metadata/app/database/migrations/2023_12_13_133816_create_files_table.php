<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->string("keyfile", 400)->primary();
            $table->string("name", 1000);
            $table->unsignedDouble("size");
            $table->integer("chunks");
            $table->boolean("is_encrypted");
            $table->string("hash", 400);
            $table->string("disperse", 400);
            $table->string("owner", 400);
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
        Schema::dropIfExists('files');
    }
}
