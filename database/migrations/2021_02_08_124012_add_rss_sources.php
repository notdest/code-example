<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\RssSource;

class AddRssSources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            UPDATE `rss_sources` SET
                `stream` = `stream` | ".RssSource::STREAM_COSMO."
            WHERE `link` IN (
                'https://www.starhit.ru/rss/',
                'http://7days.ru/rss/all/',
                'http://www.spletnik.ru/rss-anews/main',
                'https://teleprogramma.pro/ya-feed/'
            ) ;");


        $insert = function ($name,$stream,$link){
            DB::insert( "INSERT INTO `rss_sources` (`name`,`stream`,`link`) VALUES ( '$name', $stream , '$link')");
        };

        $insert('kp.ru',                RssSource::STREAM_COSMO,        'http://kp.ru/rss/allsections.xml');
        $insert('hellomagazine.com',    RssSource::STREAM_COSMO,        'https://ru.hellomagazine.com/rss.xml');
        $insert('sobesednik.ru',        RssSource::STREAM_COSMO,        'https://sobesednik.ru/rss.xml');
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
