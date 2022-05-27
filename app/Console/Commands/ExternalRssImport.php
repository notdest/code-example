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
    protected $signature = 'rss:import {debugId=0} {--d|debug}';
    protected $debugId   = 0;
    protected $debug     = false;

    public function handle()
    {
        set_time_limit ( 600 );
        $this->debugId = $this->argument('debugId');        // отладка конретного источника, вывод в stdout вместо записи в БД
        $this->debug   = $this->option('debug');            // отладка на бою - что и как долго выполняется

        $debug  = [$this,'debugMessage'];
        $debug("\nstarted the script");

        if ($this->debugId > 0){
            $sources = DB::select('SELECT * FROM `rss_sources` WHERE `id` = '.intval($this->debugId).';');
        }else{
            $sources = DB::select('SELECT * FROM `rss_sources` WHERE `active` > 0;');
        }
        $debug('Got '.count($sources).' sources');

        $classifier = RssCategory::getClassifier();
        $debug('got a classifier');

        $httpClient     = new \GuzzleHttp\Client();
        $exceptions     = [];
        $translate      = false;
        $downloadTexts  = false;

        foreach ($sources as $source ) {
            $debug("\nstarted processing ".$source->link);
            $class      = '\App\Console\Commands\rss_adapters\\'.$source->adapter;
            $adapter    = new  $class();

            try {
                $response   = $httpClient->request('GET', $source->link,[
                    'headers' => [
                        'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.60 Safari/537.36',
                    ]
                ]);

                $plainXml   = $adapter->filter($response->getBody()->getContents());
                $xml        = simplexml_load_string($plainXml, "SimpleXMLElement", LIBXML_NOCDATA);
            } catch (\Throwable $e) {
                $exceptions[]   = $e;
                $debug("Download error");
                continue;
            }
            $debug("Successfully downloaded");

            $rawItems   = $adapter->getItems($xml);
            $items      = $ids  = [];
            foreach ($rawItems as $rawItem) {                   // тут приводим записи к общему виду, без обращений к БД
                try {
                    $item   = $adapter->extractItem($rawItem,$source->id);

                    if(count($item->categories)){
                        $unknown = $item->categories;
                        [$item->categories,$item->unknownCategories]  = $classifier($source->id,$unknown);
                    }else{
                        $item->categories           = $source->default_categories;
                        $item->unknownCategories    = [];
                    }
                    $ids[]   = $item->externalId;
                    $items[] = $item;
                } catch (\Throwable $e) {
                    $exceptions[]   = $e;

                    if(count($exceptions)>10){      // много ошибок, что-то поменялось в фиде
                        continue 2;
                    }else{
                        continue;
                    }
                }
            }
            $debug("Completed pre-processing");
                                                                    // получаем за один запрос уже сохранённые записи
            $savedIds   = \DB::table('rss_imported')->whereIn('external_id', $ids)->pluck('external_id')->all();
            $savedIds   = array_flip($savedIds);
            $debug("Found existing records - ".count($savedIds)." items");

            $insertion  = [];
            foreach ($items as $item) {                                         // Тут уже формируем массив на вставку
                if ( !isset($savedIds[$item->externalId]) || ($this->debugId > 0)){
                    if($source->foreign > 0){
                        $translate = true;
                    }

                    if(strlen($source->text_adapter) > 0 ){
                        $downloadTexts  = true;
                    }

                    $insertion[]    = [
                        'pub_date'              => $item->pubDate,
                        'source_id'             => $source->id,
                        'title'                 => $item->title,
                        'foreign_title'         => $item->foreignTitle,
                        'link'                  => $item->link,
                        'categories'            => $item->categories,
                        'unknown_categories'    => mb_substr( implode(', ',$item->unknownCategories) ,0,255,'UTF-8'),
                        'external_id'           => $item->externalId,
                        'translate'             => $item->translate,
                        'original_text'         => $item->original_text,
                        'translated_text'       => $item->translated_text,
                    ];
                }
            }
            $debug("Formed an array for insertion - ".count($insertion)." items");

            if(count($insertion)){
                if ($this->debugId > 0){
                    print_r($insertion);
                }else {
                    \DB::table('rss_imported')->insert($insertion);
                }
                $debug("Inserted records");
            }
        }

        if($translate){
            dispatch( new \App\Jobs\translateRss());
            $debug("Dispatch translator");
        }

        if($downloadTexts){
            dispatch( new \App\Jobs\downloadRssText());
            $debug("Dispatch text downloader");
        }

        foreach ($exceptions as $exception) {   // когда отработали сообщаем об ошибках в логгер
            throw $exception;
        }

        return Command::SUCCESS;
    }

    private function debugMessage($msg){
        if($this->debug){
            echo $msg.date('   - H:i:s (').(round(memory_get_usage()/(1024*1024),2))."Mb)"."\n";
        }
    }
}
