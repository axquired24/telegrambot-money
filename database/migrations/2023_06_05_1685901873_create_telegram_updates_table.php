<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTelegramUpdatesTable extends Migration
{
    public function up()
    {
        Schema::create('telegram_updates', function (Blueprint $table) {

            $table->integer('update_id');
            $table->text('message');
            $table->tinyInteger('has_error')->default(0);
            $table->datetime('parsed_at')->nullable();
            $table->tinyInteger('error_solved')->default(0);
            $table->primary('update_id');

        });
    }

    public function down()
    {
        Schema::dropIfExists('telegram_updates');
    }
}
