<?php

namespace App\Console\Commands;

use App\RssCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ExternalRssImport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rss:import {debugId=0}';
    protected $debugId   = 0;

    public function handle()
    {
        set_time_limit ( 600 );
        $this->debugId = $this->argument('debugId');        // отладка конретного источника, вывод в stdout вместо записи в БД

        if ($this->debugId > 0){
            $sources = DB::select('SELECT * FROM `rss_sources` WHERE `id` = '.intval($this->debugId).';');
        }else{
            $sources = DB::select('SELECT * FROM `rss_sources` WHERE `active` > 0;');
        }

        $classifier = RssCategory::getClassifier();

        $httpClient = new \GuzzleHttp\Client();
        $exceptions = [];

        foreach ($sources as $source ) {
            $class      = '\App\Console\Commands\rss_adapters\\'.$source->adapter;
            $adapter    = new  $class();

            try {
                $response   = $httpClient->request('GET', $source->link);
                $plainXml   = $adapter->filter($response->getBody()->getContents());
                $xml        = simplexml_load_string($plainXml, "SimpleXMLElement", LIBXML_NOCDATA);
            } catch (\Throwable $e) {
                $exceptions[]   = $e;
                continue;
            }

            $items  = $adapter->getItems($xml);


            foreach ($items as $rawItem) {
                try {
                    $item   = $adapter->extractItem($rawItem,$source->id);

                    if(count($item->categories)){
                        $unknown = $item->categories;
                        list($categories,$unknown)  = $classifier($source->id,$unknown);
                    }else{
                        $categories = 0;
                        $unknown    = [];
                    }

                    if ($this->itemToSave($item->externalId)){
                        $this->itemSave([
                            'pub_date'    => $item->pubDate,
                            'source_id'   => $source->id,
                            'title'       => $item->title,
                            'link'        => $item->link,
                            'categories'  =>  $categories,
                            'unknown_categories' => mb_substr( implode(', ',$unknown) ,0,255,'UTF-8'),
                            'external_id' => $item->externalId,
                        ]);
                    }

                } catch (\Throwable $e) {
                    $exceptions[]   = $e;

                    if(count($exceptions)>10){      // много ошибок, что-то поменялось в фиде
                        continue 2;
                    }else{
                        continue;
                    }
                }
            }
        }

        foreach ($exceptions as $exception) {   // когда отработали сообщаем об ошибках в логгер
            throw $exception;
        }
    }

    private function itemSave($item){
        if ($this->debugId > 0){
            print_r($item);
            return true;
        }
        return \DB::table('rss_imported')->insert($item);
    }

    private function itemToSave($externalId){
        if ($this->debugId > 0){
            return true;
        }

        $query = \DB::table('rss_imported')->where('external_id', $externalId);
        return !(bool)$query->first();
    }
}
