<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveinternetPages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('liveinternet_pages', function (Blueprint $table) {
            $table->id();

            $table->tinyInteger( 'site');
            $table      ->string('page', 1000);
            $table      ->string('page_hash', 40);
            $table      ->date(  'day');
            $table  ->integer(   'views');

            $table->index([ 'day','page_hash']);
            $table->index([ 'page_hash','site','day']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('liveinternet_pages');
    }
}
