<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public static function getClassifier(){

        $lines = DB::select('SELECT `sourceId`,`name`,`category` FROM `rss_categories`');

        $dictionary = [];
        foreach ($lines as $line) {
            $dictionary[$line->sourceId][$line->name]   = $line->category;
        }

        $classifier =   function ($source,$categories) use($dictionary){
                            $ourCategories  = 0;
                            $unknown        = [];

                            foreach ($categories as $category) {
                                $category = trim($category);
                                if(isset($dictionary[$source][$category])){
                                    $ourCategories = $ourCategories | $dictionary[$source][$category];
                                }else{
                                    $unknown[]  = $category;
                                }
                            }

                            return [$ourCategories,$unknown];
                        };

        return $classifier;
    }
}
