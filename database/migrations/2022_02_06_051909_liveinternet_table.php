<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LiveinternetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('liveinternet', function (Blueprint $table) {
            $table->id();

            $table->enum(   'type', ['zen', 'social', 'yandex']);
            $table->string( 'site', 190);
            $table->date(   'day');
            $table->integer('views');

            $table->index(['type', 'day']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('liveinternet');
    }
}
