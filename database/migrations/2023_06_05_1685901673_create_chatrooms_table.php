<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatroomsTable extends Migration
{
    public function up()
    {
        Schema::create('chatrooms', function (Blueprint $table) {

            $table->integer('id');
            $table->string('type');
            $table->string('title');
            $table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('chatrooms');
    }
}
