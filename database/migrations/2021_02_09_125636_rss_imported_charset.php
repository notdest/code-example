<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RssImportedCharset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared(
            "ALTER TABLE `rss_imported`
                CHANGE `title` `title` varchar(255) COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `author`,
                CHANGE `description` `description` longtext COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `title`;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
