<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $table    = 'rss_imported';
    public $timestamps  = false;

    public function source(){
        return $this->belongsTo('App\RssSource')->withDefault([
            'name'  => '',
        ]);
    }


    const CATEGORY_AUTO         = 1;
    const CATEGORY_ASTROLOGIA   = 2;
    const CATEGORY_HOUSE        = 4;
    const CATEGORY_FOOD         = 8;
    const CATEGORY_HEALTH       = 16;
    const CATEGORY_CELEBRITIES  = 32;
    const CATEGORY_CORONAVIRUS  = 64;
    const CATEGORY_CULTURE      = 128;
    const CATEGORY_FASHION      = 256;
    const CATEGORY_SCIENCE      = 512;
    const CATEGORY_NEWS         = 1024;
    const CATEGORY_SOCIETY      = 2048;
    const CATEGORY_POLITICS     = 4096;
    const CATEGORY_INCIDENTS    = 8192;
    const CATEGORY_PSYCHOLOGY   = 16384;
    const CATEGORY_TRAVELS      = 32768;
    const CATEGORY_LIFESTYLE    = 65536;
    const CATEGORY_TESTS        = 131072;
    const CATEGORY_TECHNOLOGY   = 262144;
    const CATEGORY_SHOPPING     = 524288;
    const CATEGORY_ECONOMY      = 1048576;
    const CATEGORY_VIDEO        = 2097152;


    public static $categories  = [
        self::CATEGORY_AUTO         => "Авто",
        self::CATEGORY_ASTROLOGIA   => "Астороголия",
        self::CATEGORY_VIDEO        => "Видео",
        self::CATEGORY_HOUSE        => "Дом",
        self::CATEGORY_FOOD         => "Еда",
        self::CATEGORY_HEALTH       => "Здоровье и спорт",
        self::CATEGORY_CELEBRITIES  => "Знаменитости",
        self::CATEGORY_CORONAVIRUS  => "Коронавирус",
        self::CATEGORY_CULTURE      => "Культура",
        self::CATEGORY_FASHION      => "Мода и красота",
        self::CATEGORY_SCIENCE      => "Наука",
        self::CATEGORY_NEWS         => "Новости",
        self::CATEGORY_SOCIETY      => "Общество",
        self::CATEGORY_POLITICS     => "Политика и мир",
        self::CATEGORY_INCIDENTS    => "Происшествия",
        self::CATEGORY_PSYCHOLOGY   => "Психология и семья",
        self::CATEGORY_TRAVELS      => "Путешествия",
        self::CATEGORY_LIFESTYLE    => "Стиль жизни",
        self::CATEGORY_TESTS        => "Тесты",
        self::CATEGORY_TECHNOLOGY   => "Технологии",
        self::CATEGORY_SHOPPING     => "Шопинг",
        self::CATEGORY_ECONOMY      => "Экономика",
    ];

    public function getCategoryAttribute()
    {
        $ret    = '';

        foreach (self::$categories as $k => $category) {
            if($k & $this->categories){
                $ret .= ($ret) ? ',':'';
                $ret .= $category;
            }
        }

        return $ret;
    }
}
