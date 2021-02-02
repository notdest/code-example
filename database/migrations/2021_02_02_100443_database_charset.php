<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DatabaseCharset extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('ALTER DATABASE `parser` COLLATE utf8mb4_general_ci;');
        DB::unprepared("ALTER TABLE `posts` COLLATE 'utf8mb4_general_ci';");
        DB::unprepared("ALTER TABLE `posts` CHANGE `text` `text` text COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `createdTime`;");
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
