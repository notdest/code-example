<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RssExternalIdIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rss_imported', function (Blueprint $table) {
            DB::unprepared("CREATE INDEX external_id_index ON rss_imported (external_id(190));");
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
            DB::unprepared("DROP INDEX `external_id_index` ON rss_imported;");
        });
    }
}
