<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRegularEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('regular_events', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('title',255);
            $table->tinyInteger('category')->default(0);
            $table->dateTime('start');
            $table->dateTime('end')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('regular_events');
    }
}
