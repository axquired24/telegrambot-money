<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFromsTable extends Migration
{
    public function up()
    {
        Schema::create('froms', function (Blueprint $table) {

            $table->integer('id');
            $table->string('username');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->primary('id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('froms');
    }
}
