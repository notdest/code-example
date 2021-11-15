<?php
namespace App\Console\Commands\rss_adapters;

class text_in_description extends common {

    public function filter($xml){
        return $xml;
    }

    public function extractItem($rawItem,$sourceId){
        $item               = parent::extractItem($rawItem,$sourceId);
        $item->foreignTitle = $item->title;
        $item->title        = '';

        $item->original_text = trim(strip_tags($rawItem->description));

        return $item;
    }


    protected function getDefaultItem(){
        $item               = parent::getDefaultItem();
        $item->translate    = 1;
        return $item;
    }
}
