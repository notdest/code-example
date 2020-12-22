<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

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
        $rssList = [
            'https://www.woman.ru/rss-feeds/rss.xml',
            'https://www.wday.ru/rss-feeds/rss.xml',
            'https://www.spletnik.ru/rss-anews/main',
            'https://www.spletnik.ru/rss-yandex/main',
            'https://7days.ru/rss/all/',
            'https://www.passion.ru/rss.xml',
            'https://www.wmj.ru/rss',
            'https://woman.rambler.ru/rss/fashion/',
            'https://woman.rambler.ru/rss/yandex/',
            'https://aif.ru/rss/all.php',
            'https://www.starhit.ru/rss/',
            'https://teleprogramma.pro/ya-feed/',
            'https://lenta.ru/rss',
            'https://lifehacker.ru/feed/',
            'https://www.iphones.ru/feed',
            'https://www.factroom.ru/feed',
            'https://www.ok-magazine.ru/rss.xml',
            'https://www.psychologies.ru/rss/',
            'https://www.marieclaire.ru/rss/yandex/',
            'https://dni.ru/rss.xml',
            'https://www.buro247.ru/xml/rss.xml',
            'https://www.vogue.ru/feed/all-content/rss',
            'https://www.elle.ru/rss/elle_ru/rss.xml',
            'https://www.glamour.ru/rss/wifiru.xml',
            'https://www.wonderzine.com/feeds/posts.atom',
            'https://daily.afisha.ru/rss/',
            'https://st.kp.yandex.net/rss/news.rss',
            'https://www.film.ru/rss.xml',
        ];

        $httpClient = new \GuzzleHttp\Client();

        foreach ($rssList as $url) {
            $response = $httpClient->request('GET', $url);

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
                            'author'      => isset($itemArr['author']) ? $itemArr['author'] : '',
                            'title'       => isset($itemArr['title']) ? $itemArr['title'] : '',
                            'description' => isset($itemArr['description']) ? ($itemArr['description'] ?: $this->searchDescription($itemXml)) : '',
                            'link'        => isset($itemArr['link']) ? $itemArr['link'] : '',
                            'category'    => isset($itemArr['category']) ? (is_array($itemArr['category']) ? implode(', ', $itemArr['category']) : $itemArr['category']) : '',
                            'source'      => preg_replace('~(https:\/\/[a-z0-9\.]+\/)(.+)~', '$1', $url),
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
