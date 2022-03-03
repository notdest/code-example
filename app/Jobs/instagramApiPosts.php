<?php

namespace App\Jobs;

use App\Http\Controllers\StoryController;
use App\Source;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class instagramApiPosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;
    public $maxExceptions = 1;

    protected $baseDir = '/var/www/public/';
    protected $cacheDir;
    protected $httpClient;
    protected $headers;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function middleware()
    {
        return [(new WithoutOverlapping())->dontRelease()->expireAfter(600)];
    }
    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){
        $timeLimit  = time() + 50;

        $config     = new \App\Configs\Instagram();
        if ( ($config->enabled == 0)&&($config->enableStories == 0) ) return;

        $this->initImgCache();
        $this->initHttpClient();

        $sources    = DB::table('sources')  ->where('type','instagram')
                                            ->where('active','>',0)
                                            ->orderBy('id')->get()->keyBy('id');

        while(time() < $timeLimit){
            [$source,$type,$sleep] = $this->waitAndSource($sources,$config->feedMaxPages );

            if(time()+$sleep > $timeLimit) break;
            if($sleep > 0){
                sleep($sleep);
            }

            $this->writeCall($source,$type);
            if($source->userId <= 0){
                $this->takeUserId($source);
                $this->writeCall($source,'story');
                continue;
            }

            if(($config->enabled > 0) && ($type === 'post')){       // TODO: 'post' вынести в константу, если приживётся
                $this->takePosts($source->userId,$source->id);
            }

            if(($config->enableStories > 0) && ($type === 'story')){
                $this->takeStories($source->userId,$source->id);
            }
        }
    }


    protected function initImgCache(){
        $this->cacheDir = 'img_cache/'.date("Y_m");
        if(!file_exists($this->baseDir.$this->cacheDir)){
            mkdir($this->baseDir.$this->cacheDir);
        }
    }

    protected function initHttpClient(){
        $this->httpClient = new \GuzzleHttp\Client();

        $token      = env('RAPIDAPI_TOKEN');
        $this->headers = [
            'x-rapidapi-host'   => "instagram39.p.rapidapi.com",
            'x-rapidapi-key'    => $token,
        ];
    }

    protected function saveImage($post){
        $imgUrl     = $post->node->display_url;
        $localImg   = "{$this->cacheDir}/{$post->node->shortcode}.jpg";
        if(!file_exists($this->baseDir.$localImg)){
            $this->httpClient->request('GET', $imgUrl, ['sink' => $this->baseDir.$localImg]);
        }
        return '/'.$localImg;
    }

    // метод получает численный userId, и либо записывает его источнику, либо деактивирует источник при ошибке
    protected function takeUserId($source){
        $url        = "https://instagram39.p.rapidapi.com/getUserId?username={$source->code}";
        $response   = $this->httpClient->request('GET', $url, ['headers' => $this->headers])->getBody();;
        $data       = json_decode($response);

        $obj    = Source::find($source->id);
        if($data->success === true){
            $obj->userId    = $data->data->id;
        }else{
            $obj->active    = 0;
        }
        $obj->save();
    }

    // получает посты, и новые записывает в БД
    protected function takePosts($instagramId,$sourceId){
        $url    = "https://instagram39.p.rapidapi.com/getFeed?user_id={$instagramId}";

        $response   = $this->httpClient->request('GET', $url, ['headers' => $this->headers])->getBody();;
        $data       = json_decode($response);

        $ids        = array_map( function ($v){return intval($v->node->id);}, $data->data->edges);
        $localPosts = DB::table('posts')->whereIn('numericalId', $ids)->pluck('numericalId')->all();

        $insertion = [];
        foreach ($data->data->edges as $post) {
            if(in_array($post->node->id,$localPosts)){
                continue;
            }

            $image  = $this->saveImage($post);
            $newRow = [
                'sourceId'      => $sourceId,
                'numericalId'   => $post->node->id,
                'postId'        => $post->node->shortcode,
                'createdTime'   => date("Y-m-d H:i:s",$post->node->taken_at_timestamp),
                'text'          => $post->node->edge_media_to_caption->edges[0]->node->text ?? '',
                'image'         => $image,
            ];

            $insertion[] = $newRow;
        }

        if(count($insertion)>0){
            DB::table('posts')->insert( $insertion );
        }
    }

    // получает сторис, и новые записывает в БД
    protected function takeStories($instagramId,$sourceId){
        $url        = "https://instagram39.p.rapidapi.com/getStories?user_id={$instagramId}";
        $response   = $this->httpClient->request('GET', $url, ['headers' => $this->headers])->getBody();
        $data       = json_decode($response);

        if(!isset($data->data->items)) return;

        $stories        = $data->data->items;
        $ids            = array_map(function ($v){return $v->pk;},$stories);
        $localStories   = DB::table('stories')->whereIn('storyId', $ids)->pluck('storyId')->all();

        $insertion = [];
        foreach ($stories as $story) {
            if(in_array($story->pk,$localStories)){
                continue;
            }

            $image  = $this->saveStoryImage($story);
            $video  = $this->saveStoryVideo($story);

            $newRow = [
                "sourceId"      => $sourceId,
                "storyId"       => $story->pk,
                "createdTime"   => date("Y-m-d H:i:s",$story->taken_at),
                "type"          => (isset($story->video_versions)) ? StoryController::VIDEO : StoryController::PHOTO,
                "image"         => $image,
                "video"         => $video,
                "duration"      => (isset($story->video_duration)) ? round($story->video_duration) : 0 ,
            ];

            $insertion[] = $newRow;
        }

        if(count($insertion)>0){
            DB::table('stories')->insert( $insertion );
        }
    }


    protected function saveStoryImage($story){
        $image      = reset($story->image_versions2->candidates);
        $imgUrl     = $image->url;
        $localImg   = "{$this->cacheDir}/{$story->pk}.jpg";
        if(!file_exists($this->baseDir.$localImg)){
            $this->httpClient->request('GET', $imgUrl, ['sink' => $this->baseDir.$localImg]);
        }
        return '/'.$localImg;
    }

    protected function saveStoryVideo($story){
        if(isset($story->video_versions)){
            $video      = reset($story->video_versions);
            $videoUrl   = $video->url;
        }else{
            return '';
        }

        $localVideo = "{$this->cacheDir}/{$story->pk}.mp4";
        if(!file_exists($this->baseDir.$localVideo)){
            $this->httpClient->request('GET', $videoUrl, ['sink' => $this->baseDir.$localVideo]);
        }
        return '/'.$localVideo;
    }

    private function waitAndSource($sources,$limit){
        $calls  = DB::select("SELECT * FROM `api_calls` ORDER BY `time` DESC, `sourceId` DESC, `type` DESC LIMIT $limit");

        $type   = 'post';
        if(count($calls) === 0){
            $source     = $sources->first();
        }else{
            $lastCall   = reset($calls);

            if($lastCall->type === 'story'){
                $source = $sources->firstWhere('id', '>', $lastCall->sourceId);
                if(is_null($source)){
                    $source = $sources->first();
                }
            }else{
                $type   = 'story';
                if(isset($sources[$lastCall->sourceId])){
                    $source = $sources[$lastCall->sourceId];
                }else{
                    $source = $sources->first();
                }
            }
        }

        if(count($calls)<$limit){
            $sleep  = ceil(60/$limit)+1;
        }else{
            $old    = array_pop($calls);
            $sleep  = ($old->time + 62) - time();
        }

        return [$source,$type,$sleep];
    }

    private function writeCall($source,$type){
        DB::table('api_calls')->insert([
            'time'      => time(),
            'sourceId'  => $source->id,
            'type'      => $type,
        ]);
        DB::table('api_calls')->where('time', '<', time()-200)->delete();
    }
}
