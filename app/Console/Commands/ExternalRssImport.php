<?php

namespace App\Console\Commands;

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
        $sources = DB::select('SELECT * FROM `rss_sources`;');


        $httpClient = new \GuzzleHttp\Client();

        foreach ($sources as $source ) {
            $response = $httpClient->request('GET', $source->link);

            if ($response->getStatusCode() !== 200) continue;

            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($response->getBody()->getContents(), "SimpleXMLElement", LIBXML_NOCDATA);
            libxml_clear_errors();

            $xml = (array)$xml->channel;

            if (isset($xml['item'])) {
                foreach ($xml['item'] as $itemXml) {
                    $itemArr = (array)$itemXml;
                    if ($this->itemToSave($itemArr)){
                        $this->itemSave([
                            'pub_date'    => isset($itemArr['pubDate']) ? Carbon::parse($itemArr['pubDate'])->format('Y-m-d H:i:s') : null,
                            'source_id'   => $source->id,
                            'author'      => isset($itemArr['author']) ? $itemArr['author'] : '',
                            'title'       => isset($itemArr['title']) ? $itemArr['title'] : '',
                            'description' => isset($itemArr['description']) ? ($itemArr['description'] ?: $this->searchDescription($itemXml)) : '',
                            'link'        => isset($itemArr['link']) ? $itemArr['link'] : '',
                            'category'    => isset($itemArr['category']) ? (is_array($itemArr['category']) ? implode(', ', $itemArr['category']) : $itemArr['category']) : '',
                            'external_id' => isset($itemArr['guid']) ? $itemArr['guid'] : (isset($itemArr['link']) ? $itemArr['link'] : ''),
                        ]);
                    }
                }
            }
        }
    }

    private function searchDescription($itemXml)
    {
        $description = '';

        if ((string)$itemXml->content){
            $description = (string)$itemXml->content;
        } else {
            $namespaces = $itemXml->getNameSpaces(true);
            if (isset($namespaces['yandex'])) {
                $yandex = $itemXml->children($namespaces['yandex']);
                $description = (string)$yandex->{'full-text'} ?: '';
            }
        }

        return $description;
    }

    private function itemSave($item)
    {
        return \DB::table('rss_imported')->insert($item);
    }

    private function itemToSave($itemArr)
    {
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