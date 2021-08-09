<?php
namespace App\Console\Commands\rss_adapters;

use Illuminate\Support\Carbon;

class atom extends common{


    public function getItems($xml){
        return $xml->entry;
    }

    public function filter($xml){
        return $xml;
    }

    public function extractItem($rawItem,$sourceId){
        $item   = $this->getDefaultItem();

        $item->pubDate      = Carbon::parse(reset($rawItem->updated))->format('Y-m-d H:i:s') ;
        $item->title        = reset($rawItem->title);
        $item->link         = reset($rawItem->link)['href'];
        $item->externalId   = reset($rawItem->id);

        foreach ($rawItem->category as $category) {
            $item->categories[] = reset($category)['term'];
        }

        return $item;
    }
}
