<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RssImportedSourceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rss_imported', function (Blueprint $table) {
            $table->unsignedInteger('source_id')->after('pub_date')->nullable();

            $table->foreign('source_id')->references('id')->on('rss_sources')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rss_imported', function (Blueprint $table) {
            $table->dropForeign(['source_id']);

            $table->dropColumn('source_id');
        });
    }
}
