<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('topic_id')->nullable();
            $table->bigInteger('chatroom_id');
            $table->string('name');

            $table->unique(['topic_id', 'chatroom_id']);
            $table->foreign('chatroom_id')->references('id')->on('chatrooms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topics');
    }
};
