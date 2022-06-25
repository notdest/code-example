<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LiveinternetAddSlices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(
            "ALTER TABLE `liveinternet`
                CHANGE `type` `type` enum('zen','social','yandex','ru', 's_googl', 'n_y', 'n_g', 'android_google',
                                                        'smi2', 'vk', 'm.vk.com', 'facebook', 'm.facebook.com', 'ok')
                COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `id`;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement(
            "ALTER TABLE `liveinternet`
                CHANGE `type` `type` enum('zen','social','yandex','ru', 's_googl', 'n_y', 'n_g', 'android_google')
                COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `id`;");
    }
}
