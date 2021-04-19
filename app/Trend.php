<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Trend extends Model
{
    const FEED_RU = 1;
    const FEED_US = 2;

    const URLS = [
        self::FEED_RU   => 'https://trends.google.ru/trends/trendingsearches/daily/rss?geo=RU',
        self::FEED_US   => 'https://trends.google.ru/trends/trendingsearches/daily/rss?geo=US',
    ];

    const NAMES = [
        self::FEED_RU   => 'Россия',
        self::FEED_US   => 'Соединенные штаты',
    ];

    const SORTING_DATE      = 0;
    const SORTING_TRAFFIC   = 1;

    protected $fillable     = ['pubDate', 'title', 'feed', 'data', 'traffic'];


    public function getTrafficAttribute(){
        $data   = json_decode($this->data);
        return (isset($data->approx_traffic)) ? (int) str_replace([',','+'],'',$data->approx_traffic) : 0;
    }

    public function getNewsAttribute(){
        $data   = json_decode($this->data);
        $news   = $data->news_item ?? [];
        return (is_array($news)) ? $news : [$news];
    }
}
