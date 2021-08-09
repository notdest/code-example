<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ForeignSources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rss_sources', function (Blueprint $table) {
            $table->tinyInteger('foreign')->default(0);
            $table->unsignedInteger('default_categories')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rss_sources', function (Blueprint $table) {
            $table->dropColumn('foreign');
            $table->dropColumn('default_categories');
        });
    }
}
