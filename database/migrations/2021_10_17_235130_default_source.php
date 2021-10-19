<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DefaultSource extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("INSERT INTO `sources` (`id`, `personId`, `active`, `type`, `code`) VALUES
                                                (0,	0,	0,	'instagram',	'default');");

        DB::statement("UPDATE `sources`
                                SET `id` = '0'
                                WHERE `code` = 'default';");// не хочет нормально нуль вставлять
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DELETE FROM `sources` WHERE `id` = '0';");
    }
}
