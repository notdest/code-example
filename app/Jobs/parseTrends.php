<?php

namespace App\Jobs;

use App\Trend;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class parseTrends implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new \GuzzleHttp\Client();

        foreach (Trend::URLS as $feedLang => $url){
            $response   = $client->request('GET', $url);

            if ($response->getStatusCode() !== 200) continue;

            $feed   = simplexml_load_string($response->getBody()->getContents());
            foreach ($feed->channel->item as $item) {
                $data       = $item->children('ht', true);
                $pubDate    = strtotime($item->pubDate);

                Trend::updateOrCreate([
                    'pubDate'   => date("Y-m-d H:i:s",$pubDate),
                    'title'     => $item->title,
                    'feed'      => $feedLang,
                ],[
                    'data' => json_encode($data,JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
            }
        }
    }
}
