<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RssCategory extends Model
{
    protected $table    = 'rss_categories';

    public function source(){
        return $this->belongsTo('App\RssSource','sourceId')->withDefault([
            'name'  => '',
        ]);
    }


    public function getCategoryNameAttribute(){
        return \App\Article::$categories[$this->category];
    }
}
