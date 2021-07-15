<?php
namespace App\Console\Commands\rss_adapters;

use Illuminate\Support\Carbon;

class google_news{


    public function getItems($xml){
        return $xml ;
    }

    public function filter($xml){
        return $xml;
    }

    public function extractItem($rawItem,$sourceId){
        $item       = $this->getDefaultItem();
        $additional = $rawItem->children('news',true);

        $item->pubDate      = Carbon::parse(reset($additional->news->publication_date))->format('Y-m-d H:i:s');
        $item->title        = reset($additional->news->title);
        $item->link         = reset($rawItem->loc);
        $item->externalId   = $item->link;

        return $item;
    }


    private function getDefaultItem(){
        $item   = new \stdClass();
        $item->pubDate      = '';
        $item->title        = '';
        $item->link         = '';
        $item->categories   = [];
        $item->externalId   = '';
        return $item;
    }
}
