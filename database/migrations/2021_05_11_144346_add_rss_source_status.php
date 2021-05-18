<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRssSourceStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rss_sources', function (Blueprint $table) {
            $table->tinyInteger('active')->default(0);
        });

        DB::unprepared("UPDATE `rss_sources` SET `active` = '1' WHERE 1;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rss_sources', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }
}
