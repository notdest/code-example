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
}