<?php

namespace App\Jobs;

use App\Http\Controllers\StoryController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class instagramApiStories implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 1;
    public $timeout = 200;

    protected $baseDir = '/var/www/public/';
    protected $cacheDir;
    protected $httpClient;

    protected $partCount;
    protected $partNumber;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $partCount,int $partNumber)
    {
        $this->partCount    = $partCount;
        $this->partNumber   = $partNumber;
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
        $token  = env('RAPIDAPI_TOKEN');

        $config = new \App\Configs\Instagram();
        if ($config->enableStories == 0){
            return;
        }

        $this->initImgCache();
        $headers = [
            'x-rapidapi-host'   => "instagram85.p.rapidapi.com",
            'x-rapidapi-key'    => $token,
        ];

        $sources  = DB::select("SELECT * FROM `sources` WHERE `type`='instagram' AND `active`>0 AND `userId` >0 ORDER BY `id` ;");

        $chunks     = array_chunk($sources,ceil(count($sources)/$this->partCount));
        $sources    = $chunks[$this->partNumber-1];

        $ids        = array_map( function ($v){return intval($v->userId);}, $sources);
        $url        = "https://instagram85.p.rapidapi.com/account/".implode(',',$ids)."/stories";
        $response   = $this->httpClient->request('GET', $url, ['headers' => $headers])->getBody();;
        $data       = json_decode($response);

        if($data->code >= 300){
            throw new \Exception('Bad status code: '.$data->code);
        }

        foreach ($data->data as $source) {
            $storyIds  = array_map( function ($v){return intval($v->id);}, $source);

            $sourceId     = 0 ;
            $localStories   = DB::table('stories')->whereIn('storyId', $storyIds)->get();
            foreach ($localStories as $localStory) {
                if($localStory->sourceId > 0){
                    $sourceId   = $localStory->sourceId;
                    break;
                }
            }

            $localStories   = $localStories->pluck('storyId')->all();

            $insertion = [];
            foreach ($source as $story) {
                if(in_array($story->id,$localStories)){
                    continue;
                }

                $image  = $this->saveImage($story);
                $video  = $this->saveVideo($story);

                $newRow = [
                    "sourceId"      => $sourceId,
                    "storyId"       => $story->id,
                    "createdTime"   => date("Y-m-d H:i:s",$story->created_time->unix),
                    "type"          => (strlen($story->videos->standard)) ? StoryController::VIDEO : StoryController::PHOTO,
                    "image"         => $image,
                    "video"         => $video,
                    "duration"      => ($story->figures->video_duration) ? round($story->figures->video_duration) : 0 ,
                ];

                $insertion[] = $newRow;
            }

            if(count($insertion)>0){
                DB::table('stories')->insert( $insertion );
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

    protected function saveImage($story){
        $imgUrl     = $story->images->original->standard;
        $localImg   = "{$this->cacheDir}/{$story->id}.jpg";
        if(!file_exists($this->baseDir.$localImg)){
            $this->httpClient->request('GET', $imgUrl, ['sink' => $this->baseDir.$localImg]);
        }
        return '/'.$localImg;
    }

    protected function saveVideo($story){
        $videoUrl   = $story->videos->standard;

        if(!strlen($videoUrl)){
            return '';
        }
        $localVideo = "{$this->cacheDir}/{$story->id}.mp4";
        if(!file_exists($this->baseDir.$localVideo)){
            $this->httpClient->request('GET', $videoUrl, ['sink' => $this->baseDir.$localVideo]);
        }
        return '/'.$localVideo;
    }
}
