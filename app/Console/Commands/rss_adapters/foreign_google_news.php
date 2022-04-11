<?php
namespace App\Console\Commands\rss_adapters;


class foreign_google_news extends google_news {

    public function extractItem($rawItem,$sourceId){
        $item               = parent::extractItem($rawItem,$sourceId);
        $item->foreignTitle = $item->title;
        $item->title        = '';

        return $item;
    }

    protected function getDefaultItem(){
        $item               = parent::getDefaultItem();
        $item->translate    = 1;
        return $item;
    }
}
