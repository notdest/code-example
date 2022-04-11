<?php
namespace App\Console\Commands\rss_adapters;

use Illuminate\Support\Carbon;

class foreign_tmz extends foreign_common{

    public function extractItem($rawItem,$sourceId){
        $item           = parent::extractItem($rawItem,$sourceId);
        $additional     = $rawItem->children('dc',true);
        $item->pubDate  = Carbon::parse(reset($additional->date))->tz('Europe/Moscow')->format('Y-m-d H:i:s');

        return $item;
    }
}
