<?php
namespace App\Console\Commands\rss_adapters;

class foreign_common extends common {

    public function filter($xml){
        return $xml;
    }

    public function extractItem($rawItem,$sourceId){
        $item               = parent::extractItem($rawItem,$sourceId);
        $item->foreignTitle = $item->title;
        $item->title        = '';


        $additional = $rawItem->children('content',true);
        if(isset($additional->encoded)){
            $item->original_text    = strip_tags($additional->encoded);
            $item->original_text    = str_replace("\t","",$item->original_text);
            $item->original_text    = trim(preg_replace("/\n{3,}/","\n\n",$item->original_text));
        }

        return $item;
    }


    protected function getDefaultItem(){
        $item               = parent::getDefaultItem();
        $item->translate    = 1;
        return $item;
    }
}
