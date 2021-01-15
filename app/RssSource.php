<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RssSource extends Model
{
    protected $table    = 'rss_sources';
    public $timestamps  = false;

    const STREAM_COSMO      = 1;
    const STREAM_GOODHOUSE  = 2;
    const STREAM_POPMECH    = 4;
    const STREAM_ESQUIRE    = 8;
    const STREAM_GRAZIA     = 16;
    const STREAM_BAZAAR     = 32;
    //const STREAM_ROBB       = 64;

    public static $streams  = [
        self::STREAM_COSMO      => "Cosmopolitan",
        self::STREAM_GOODHOUSE  => "Домашний Очаг",
        self::STREAM_POPMECH    => "Популярная Механика",
        self::STREAM_ESQUIRE    => "Esquire",
        self::STREAM_GRAZIA     => "GRAZIA",
        self::STREAM_BAZAAR     => "Harper's Bazaar",
       // self::STREAM_ROBB       => "Robb Report",
    ];

}
