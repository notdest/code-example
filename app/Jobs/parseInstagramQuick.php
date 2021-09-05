<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class parseInstagramQuick implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 1;
    public $timeout = 290; // если работает, то должно отработать раньше time_limit в 300 сек
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
        set_time_limit ( 300 );
        $config     = new \App\Configs\Instagram();

        if ($config->enabled == 0){
            return;
        }

        $baseDir   = '/var/www/public/';
        $cacheDir = 'img_cache/'.date("Y_m");
        if(!file_exists($baseDir.$cacheDir)){
            mkdir($baseDir.$cacheDir);
        }

        $httpClient = new \GuzzleHttp\Client();
        $posts      = $this->getFeed($config);

        $usernames  = $controlUsernames = $ids = [];
        foreach ($posts as $post){
            $id     = $post->getId();
            $ids[]  =$id;

            $owner  = $post->getOwner();        // иногда не срабатывает, приходится контролировать
            if(!$owner){
                sleep(3);
                $owner = $post->getOwner();
            }

            if($owner){                         // из-за несрабатываний контролируем, был ли юзернэйм
                $username    = $owner->getUsername();
                $usernames[] =  $username;
                $controlUsernames[$id] = $username;
            }else{
                $controlUsernames[$id] = false;
            }

        }
        $localPosts = DB::table('posts')
                        ->whereIn('numericalId', $ids)
                        ->pluck('numericalId')->all();

        $sourceIds  = DB::table('sources')
                        ->where('type', 'instagram')
                        ->whereIn('code', array_unique($usernames))
                        ->pluck( 'id','code')->all();


        $insertion  = [];
        foreach ($posts as $post){
            if(in_array($post->getId(),$localPosts)){                        // если уже сохраняли то пропускаем
                break;
            }

            $imgUrl     = ($post->getImageStandardResolutionUrl())  ?: $post->getImageHighResolutionUrl();
            $httpClient->request('GET', $imgUrl, ['sink' => $baseDir.$cacheDir."/".$post->getShortCode().".jpg"]);

            $newRow = [
                'postId'        => $post->getShortCode(),
                'numericalId'   => $post->getId(),
                'createdTime'   => date("Y-m-d H:i:s",$post->getCreatedTime()),
                'text'          => $post->getCaption(),
                'image'         => '/'.$cacheDir."/".$post->getShortCode().".jpg",
            ];

            if( $controlUsernames[$post->getId()] ){           // У этого поста получен ник автора
                $username = $post->getOwner()->getUsername();

                if(isset($sourceIds[$username])) {
                    $newRow['sourceId'] = $sourceIds[$username];
                }else{
                    $source = new \App\Source();
                    $source->personId   = 0;
                    $source->active     = 1;
                    $source->type       = 'instagram';
                    $source->code       = $username;

                    $source->save();

                    $newRow['sourceId'] = $source->id;
                }

                $insertion[] = $newRow;
            }

        }

        DB::table('posts')->insert( $insertion );
        $config->dropErrors();

        if(($config->enableSubscription > 0) && ($config->lastSubscribe < strtotime('-1 hour'))){
            $config->lastSubscribe = time();
            $config->save();
            Artisan::call('Instagram:subscribe', [ 'limit' => 4 ]);
        }
    }

    protected function getFeed($config){
        $instagram  = $config->getClient();

        $posts       = [];
        $hasNextPage = true;
        $maxId  = '';
        $i      = 1;
        while ($hasNextPage){
            $result      = $instagram->getPaginateFeed(50,$maxId);
            $maxId       = $result['maxId'];
            $hasNextPage = $result['hasNextPage'];
            $posts       = array_merge($posts,$result['medias']);

            if($i >= $config->feedMaxPages) break;
            $i ++;
            sleep(rand(1,3));
        }

        return $posts;
    }
}
