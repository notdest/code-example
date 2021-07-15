<?php
namespace App\Console\Commands\rss_adapters;

use Illuminate\Support\Carbon;

// мало отличается от common, может когда-нибудь следует слить
class buro247{


    public function getItems($xml){
        return $xml->channel->item ;
    }

    public function filter($xml){
        $xml    = substr($xml,0,strpos($xml,'<turbo:analytics')) . substr($xml,strpos($xml,'turbo:analytics>')+16);

        return $xml;
    }

    public function extractItem($rawItem,$sourceId){
        $item   = $this->getDefaultItem();

        $item->pubDate      = isset($rawItem->pubDate)  ? Carbon::parse($rawItem->pubDate)->format('Y-m-d H:i:s') : null;
        $item->title        = isset($rawItem->title)    ? reset($rawItem->title): '';
        $item->link         = isset($rawItem->link)     ? reset($rawItem->link) : '';

        if (isset($rawItem->guid)){
            $guid = (array) $rawItem->guid;
            $guid = $guid[0];

            if( (strpos($guid,'.')!==false)||(strpos($guid,':')!==false) ){
                $item->externalId = $guid;
            }else{
                $item->externalId = 's'.$sourceId.'_'.$rawItem->guid;
            }
        }else{
            $item->externalId = $item->link;
        }

        if(isset($rawItem->category)){
            $item->categories = (is_array($rawItem->category)) ? $rawItem->category : [$rawItem->category];
        }

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
