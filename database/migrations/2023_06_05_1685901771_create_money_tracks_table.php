<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMoneyTracksTable extends Migration
{
    public function up()
    {
        Schema::create('money_tracks', function (Blueprint $table) {
            $table->increments('id');
            $table->float('amount', 11, 2)->nullable();
            $table->tinyInteger('is_expense')->default(1);
            $table->datetime('deleted_at')->nullable();
            $table->datetime('created_at');
            $table->datetime('updated_at');
            $table->date('trx_date')->nullable();
            $table->string('description')->nullable();
            $table->integer('from_id');
            $table->integer('topic_id')->unsigned();

            $table->foreign('from_id')->references('id')->on('froms');
            $table->foreign('topic_id')->references('id')->on('topics');
        });
    }

    public function down()
    {
        Schema::dropIfExists('money_tracks');
    }
}
