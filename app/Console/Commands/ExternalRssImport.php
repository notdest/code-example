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
    protected $signature = 'rss:import';

    public function handle()
    {
        set_time_limit ( 600 );
        $sources = DB::select('SELECT * FROM `rss_sources` WHERE `active` > 0;');

        $classifier = RssCategory::getClassifier();

        $httpClient = new \GuzzleHttp\Client();
        $exceptions = [];

        foreach ($sources as $source ) {
            try {
                $response = $httpClient->request('GET', $source->link);

                libxml_use_internal_errors(true);
                $xml = simplexml_load_string($response->getBody()->getContents(), "SimpleXMLElement", LIBXML_NOCDATA);
                libxml_clear_errors();  // ошибки типо неизвестного неймспейса, поэтому игнорируем
                                        // если не спарсится, то где-то дальше вылетит

                if(isset($xml->channel->item)){     // TODO: убрать этот костыль, один канал мы точно пропускаем
                    $items  = $xml->channel->item;
                }else{
                    continue;
                }
            } catch (\Throwable $e) {
                $exceptions[]   = $e;
                continue;
            }


            foreach ($items as $item) {
                try {
                    if(isset($item->category)){
                        $unknown = (is_array($item->category)) ? $item->category : [$item->category];
                        list($categories,$unknown)  = $classifier($source->id,$unknown);
                    }else{
                        $categories = 0;
                        $unknown    = [];
                    }

                    if ($this->itemToSave($item)){
                        $this->itemSave([
                            'pub_date'    => isset($item->pubDate) ? Carbon::parse($item->pubDate)->format('Y-m-d H:i:s') : null,
                            'source_id'   => $source->id,
                            'title'       => $item->title ?? '',
                            'link'        => $item->link  ?? '',
                            'categories'  =>  $categories,
                            'unknown_categories' => mb_substr( implode(', ',$unknown) ,0,255,'UTF-8'),
                            'external_id' => $item->guid  ?? ($item->link ?? ''),
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

    private function itemSave($item)
    {
        return \DB::table('rss_imported')->insert($item);
    }

    private function itemToSave($item)
    {
        $itemArr = (array) $item;               // Todo: переписать без приведения к массиву, так будет читаемей
                                                // вообще это дублирование кода, мы то же самое делаем при записи
        $query = \DB::table('rss_imported');

        if (isset($itemArr['guid'])){
            $query->where('external_id', $itemArr['guid']);
            if (isset($itemArr['link'])){
                $query->orWhere('external_id', $itemArr['link']);
            }
        } elseif (isset($itemArr['link'])){
            $query->where('external_id', $itemArr['link']);
        } else {
            if (isset($itemArr['title'])) {
                $query->where('title', $itemArr['title']);
            } else {
                return false; // пропускаем rss items без guid, link, title
            }
        }

        return !(bool)$query->first();
    }
}
