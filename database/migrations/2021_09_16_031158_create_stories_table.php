<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('sourceId');
            $table->unsignedBigInteger('storyId')->default(0);
            $table->dateTime('createdTime');
            $table->tinyInteger('type')->default(0);
            $table->string('image',1000);
            $table->string('video',1000);
            $table->unsignedInteger('duration')->default(0);

            $table->foreign('sourceId')->references('id')->on('sources')->onDelete('cascade')->onUpdate('cascade');
            $table->index('storyId');
            $table->index('createdTime');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stories');
    }
}
