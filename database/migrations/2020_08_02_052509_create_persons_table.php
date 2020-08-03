<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePersonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('persons', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
        });

        DB::statement('DELETE FROM sources ;'); // Без этого может выдать ошибку

        Schema::table('sources', function (Blueprint $table) {
            $table->unsignedInteger('personId')->after('id');
            $table->dropColumn('name');

            $table->foreign('personId')->references('id')->on('persons')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sources', function (Blueprint $table) {
            $table->dropForeign(['personId']);

            $table->dropColumn('personId');
            $table->string('name', 255)->after('code');
        });

        Schema::dropIfExists('persons');
    }
}
