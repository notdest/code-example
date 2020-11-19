<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableRssImported extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rss_imported', function (Blueprint $table) {
            $table->increments('id');
            $table->dateTime('pub_date')->nullable();
            $table->string('author',255);
            $table->string('title',255)->index();
            $table->longText('description');
            $table->string('link',255);
            $table->string('category',255);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rss_imported');
    }
}
