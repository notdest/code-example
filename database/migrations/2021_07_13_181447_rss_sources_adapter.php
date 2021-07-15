<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RssSourcesAdapter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rss_sources', function (Blueprint $table) {
            $table->string('adapter',255)->default('common');
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
            $table->dropColumn('adapter');
        });
    }
}
