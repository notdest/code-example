<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use InstagramScraper\Instagram;
use Phpfastcache\Helper\Psr16Adapter;

class parseInstagramQuik implements ShouldQueue
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

        $httpConfig = $config->proxy ? ['proxy' => $config->proxy] : [];

        $instagram  = Instagram::withCredentials(
            new \GuzzleHttp\Client($httpConfig),
            $config->login,
            $config->password,
            new Psr16Adapter('Files')
        );

        if($config->session){
            $instagram->loginWithSessionId($config->session);
        }else {
            $instagram->login(); // по умолчанию ищет закешированную saveSession()
            $instagram->saveSession(86400);
        }

        $posts  = $instagram->getFeed();

        $usernames  = $controlUsernames = $ids = [];
        foreach ($posts as $k => $post){
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

            $newRow = [
                'postId'        => $post->getShortCode(),
                'numericalId'   => $post->getId(),
                'createdTime'   => date("Y-m-d H:i:s",$post->getCreatedTime()+(3*3600)),
                'text'          => $post->getCaption(),
                'image'         => ($post->getImageStandardResolutionUrl())  ?: $post->getImageHighResolutionUrl(),
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
    }
}
