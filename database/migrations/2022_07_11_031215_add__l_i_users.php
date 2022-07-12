<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLIUsers extends Migration
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
                'smi2', 'vk', 'm.vk.com', 'facebook', 'm.facebook.com', 'ok', 'n-m', 's_mail', 'general', 's_yandex')
                COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `id`;");

        Schema::table('liveinternet', function (Blueprint $table) {
            $table->integer('users')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('liveinternet', function (Blueprint $table) {
            $table->dropColumn('users');
        });


        DB::statement(
            "ALTER TABLE `liveinternet`
                CHANGE `type` `type` enum('zen','social','yandex','ru', 's_googl', 'n_y', 'n_g', 'android_google',
                                        'smi2', 'vk', 'm.vk.com', 'facebook', 'm.facebook.com', 'ok', 'n-m', 's_mail')
                COLLATE 'utf8mb4_general_ci' NOT NULL AFTER `id`;");
    }
}
