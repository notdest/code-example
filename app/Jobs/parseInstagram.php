<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\DB;
use InstagramScraper\Exception\InstagramNotFoundException;
use Phpfastcache\Helper\Psr16Adapter;
use InstagramScraper\Instagram;


class parseInstagram implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 900;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $config     = app('config')['common.instagram'];
        $instagram  = Instagram::withCredentials(new \GuzzleHttp\Client(),$config->login, $config->password, new Psr16Adapter('Files'));
        $instagram->login(); // по умолчанию ищет закешированную saveSession()
        $instagram->saveSession();

        $sources  = DB::select("SELECT * FROM `sources` WHERE `type`='instagram' AND `active` > 0;");
        foreach ($sources as $source) {                                                 //перебираем все активные аккаунты в инсте
            $localPosts = DB::table('posts')->where('sourceId', $source->id)            // ищем сохраненные посты
                                            ->orderBy('id', 'desc')
                                            ->limit(5)
                                            ->pluck('postId')->all();

            try {
                $posts  =   $instagram->getMedias($source->code);                           // скачиваем 20 постов
            }catch (InstagramNotFoundException $e){
                fwrite(STDERR, "\n\n".
                    "could not download: https://www.instagram.com/{$source->code}/\n".
                    "command for deactivate: ./artisan Instagram:deactivate {$source->id}\n\n");
                continue;
            }

            sleep(rand(1,3));
            $insertion  = [];
            foreach ($posts as $post){
                if(in_array($post->getShortCode(),$localPosts)){                        // если уже сохраняли то пропускаем
                    break;
                }

                $newRow = [
                    'sourceId'      => $source->id,
                    'numericalId'   => $post->getId(),
                    'postId'        => $post->getShortCode(),
                    'createdTime'   => date("Y-m-d H:i:s",$post->getCreatedTime()+(3*3600)),
                    'text'          => $post->getCaption(),
                    'image'         => $post->getImageStandardResolutionUrl(),
                ];

                $insertion[] = $newRow;
            }

            DB::table('posts')->insert( array_reverse($insertion) );                    // сохраняем в хронологическом порядке
        }
    }
}
