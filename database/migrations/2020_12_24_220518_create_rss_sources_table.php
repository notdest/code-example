<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\RssSource;

class CreateRssSourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rss_sources', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('link', 255)->unique();
            $table->unsignedSmallInteger('stream');
        });

        $insert = function ($name,$stream,$link){
            DB::insert( "INSERT INTO `rss_sources` (`name`,`stream`,`link`) VALUES ( '$name', $stream , '$link')");
        };

        $insert('woman.ru',         RssSource::STREAM_COSMO,        'https://www.woman.ru/rss-feeds/rss.xml');
        $insert('Passion.ru',       RssSource::STREAM_COSMO,        'https://www.passion.ru/rss.xml');
        $insert('wday.ru',          RssSource::STREAM_COSMO,        'http://www.wday.ru/rss-feeds/rss.xml');
        $insert('wmj.ru',           RssSource::STREAM_COSMO,        'https://www.wmj.ru/rss');
        $insert('woman.rambler.ru', RssSource::STREAM_COSMO,        'https://woman.rambler.ru/rss/fashion/');
        $insert('woman.rambler.ru', RssSource::STREAM_COSMO,        'https://woman.rambler.ru/rss/yandex/');
        $insert('7days.ru',         RssSource::STREAM_GOODHOUSE | RssSource::STREAM_GRAZIA, 'http://7days.ru/rss/all/');
        $insert('aif.ru',           RssSource::STREAM_GOODHOUSE,    'https://aif.ru/rss/all.php');
        $insert('starhit.ru',       RssSource::STREAM_GOODHOUSE,    'https://www.starhit.ru/rss/');
        $insert('teleprogramma.pro',RssSource::STREAM_GOODHOUSE,    'https://teleprogramma.pro/ya-feed/');
        $insert('Lenta.ru',         RssSource::STREAM_POPMECH,      'https://lenta.ru/rss');
        $insert('Lifehacker.ru',    RssSource::STREAM_POPMECH,      'https://lifehacker.ru/feed/');
        $insert('iphones.ru',       RssSource::STREAM_POPMECH,      'https://www.iphones.ru/feed');
        $insert('factroom.ru',      RssSource::STREAM_POPMECH,      'https://www.factroom.ru/feed');
        $insert('spletnik.ru',      RssSource::STREAM_GRAZIA | RssSource::STREAM_BAZAAR, 'http://www.spletnik.ru/rss-anews/main');
        $insert('Ok-magazine.ru',   RssSource::STREAM_GRAZIA,       'https://www.ok-magazine.ru/rss.xml');
        $insert('psychologies.ru',  RssSource::STREAM_COSMO,        'https://www.psychologies.ru/rss/');
        $insert('Marieclaire.ru',   RssSource::STREAM_GRAZIA,       'https://www.marieclaire.ru/rss/yandex/');
        $insert('Dni.ru',           RssSource::STREAM_GRAZIA,       'https://dni.ru/rss.xml');
        $insert('Spletnik.ru',      RssSource::STREAM_BAZAAR,       'http://www.spletnik.ru/rss-yandex/main');
        $insert('Buro247.ru',       RssSource::STREAM_BAZAAR,       'https://www.buro247.ru/xml/rss.xml');
        $insert('Vogue.ru',         RssSource::STREAM_BAZAAR,       'https://www.vogue.ru/feed/all-content/rss');
        $insert('Elle.ru',          RssSource::STREAM_BAZAAR,       'https://www.elle.ru/rss/elle_ru/rss.xml');
        $insert('glamour.ru',       RssSource::STREAM_BAZAAR,       'https://www.glamour.ru/rss/wifiru.xml');
        $insert('Wonderzine.com',   RssSource::STREAM_ESQUIRE,      'https://www.wonderzine.com/feeds/posts.atom');
        $insert('Afisha.ru',        RssSource::STREAM_ESQUIRE,      'https://daily.afisha.ru/rss/');
        $insert('Kinopoisk.ru',     RssSource::STREAM_ESQUIRE,      'https://st.kp.yandex.net/rss/news.rss');
        $insert('film.ru',          RssSource::STREAM_ESQUIRE,      'https://www.film.ru/rss.xml');

    }



    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rss_sources');
    }
}
