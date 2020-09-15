<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DefaultPerson extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("INSERT INTO `persons` (`id`, `name`) VALUES
                            ( 0,	'Default');");

        DB::statement("UPDATE `persons` SET
                            `id` = '0'
                            WHERE `name` = 'Default';");// не хочет нормально нуль вставлять
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
