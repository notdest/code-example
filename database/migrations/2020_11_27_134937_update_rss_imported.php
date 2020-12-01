<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRssImported extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rss_imported', function (Blueprint $table) {
            $table->string('source',255);
            $table->string('external_id',255);
        });

        \DB::statement("UPDATE `rss_imported` SET `external_id` = `link`");

        $items = \DB::table('rss_imported')->get();
        $items->each(function ($item, $key) {
            $source = preg_replace('~(https:\/\/[a-z0-9\.]+\/)(.+)~', '$1', $item->link);
            \DB::statement("UPDATE `rss_imported` SET `source` = '".$source."' WHERE `id` = ".$item->id);
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
            $table->dropColumn('source');
            $table->dropColumn('external_id');
        });
    }
}
