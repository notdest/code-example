<?php

namespace App\Jobs;

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
    public function handle()
    {
        $token      = env('RAPIDAPI_TOKEN');
        $startTime  = time();

        $config     = new \App\Configs\Instagram();

        if ($config->enabled == 0){
            return;
        }
        $this->initImgCache();
        $this->headers = [
            'x-rapidapi-host'   => "instagram39.p.rapidapi.com",
            'x-rapidapi-key'    => $token,
        ];

        $allSources  = DB::select("SELECT * FROM `sources` WHERE `type`='instagram' AND `active`>0 ORDER BY `id` ;");

        $length = intval($config->feedMaxPages );                   // разбиваем источники на максимально равные куски в рамках предела
        $count  = ceil(count($allSources)/$length);
        if($count <= 0){
            return;
        }
        $chunks = array_chunk($allSources, ceil(count($allSources)/$count)  );

        $minutes    = ( (date('j')*24)+date('G') )*60 + date('i');  // берем кусок в зависимости от минуты месяца
        $sources    = $chunks[$minutes % $count];

        foreach ($sources as $key => $source) {
            $time   = time()-$startTime;
            if($time < ($key*3)){
                sleep(($key*3)-$time);
            }

            if($source->userId <= 0){       // проставляем userId вместо получения постов, чтобы уместится по времени
                $this->takeUserId($source);
                continue;
            }

            $url    = "https://instagram39.p.rapidapi.com/getFeed?user_id={$source->userId}";

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
                    'sourceId'      => $source->id,
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
    }


    protected function initImgCache(){
        $this->cacheDir = 'img_cache/'.date("Y_m");
        if(!file_exists($this->baseDir.$this->cacheDir)){
            mkdir($this->baseDir.$this->cacheDir);
        }

        $this->httpClient = new \GuzzleHttp\Client();
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
}
