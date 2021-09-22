<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\StoryController;

class parseInstagramStories implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries   = 1;
    public $timeout = 290;

    protected $baseDir = '/var/www/public/';
    protected $cacheDir;
    protected $httpClient;
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

        if (($config->enabled == 0)||($config->enableStories == 0)){
            return;
        }

        $this->initImgCache();

        $instagram      = $config->getClient();
        $userStories    = $instagram->getStories();

        list($localStories,$sourceIds) = $this->findStored($userStories);


        $insertion  = [];
        foreach ($userStories as $userStory) {
            $owner      = $userStory->getOwner();
            $stories    = $userStory->getStories();

            $sourceId   = $this->getSourceId($sourceIds,$owner->getUsername());

            foreach ($stories as $story) {
                if(in_array($story->getId(),$localStories)){                        // если уже сохраняли, то пропускаем
                    break;
                }

                $image  = $this->saveImage($story);
                $video  = $this->saveVideo($story);

                $newRow = [
                    "sourceId"      => $sourceId,
                    "storyId"       => $story->getId(),
                    "createdTime"   => date("Y-m-d H:i:s",$story->getCreatedTime()),
                    "type"          => ($story->getType() === 'image' ) ? StoryController::PHOTO : StoryController::VIDEO,
                    "image"         => $image,
                    "video"         => $video,
                    "duration"      => ($story->getType() === 'image' ) ? 0 : round($story->getVideoDuration()),
                ];

                $insertion[] = $newRow;
            }

            if(count($insertion)>50){
                DB::table('stories')->insert( $insertion );
                $insertion  = [];
            }
        }

        if(count($insertion)>0){
            DB::table('stories')->insert( $insertion );
        }
        $config->dropErrors();
    }

    protected function initImgCache(){
        $this->cacheDir = 'img_cache/'.date("Y_m");
        if(!file_exists($this->baseDir.$this->cacheDir)){
            mkdir($this->baseDir.$this->cacheDir);
        }

        $this->httpClient = new \GuzzleHttp\Client();
    }

    protected function saveImage($story){
        $imgUrl     = ($story->getImageStandardResolutionUrl())  ?: $story->getImageHighResolutionUrl();
        $localImg   = $this->cacheDir."/".$story->getId().".jpg";
        if(!file_exists($this->baseDir.$localImg)){
            $this->httpClient->request('GET', $imgUrl, ['sink' => $this->baseDir.$localImg]);
        }
        return '/'.$localImg;
    }

    protected function saveVideo($story){
        if($story->getType() === 'image' ){
            return '';
        }
        $videoUrl   = ($story->getVideoLowResolutionUrl())  ?: $story->getVideoStandardResolutionUrl();
        $localVideo = $this->cacheDir."/".$story->getId().".mp4";
        if(!file_exists($this->baseDir.$localVideo)){
            $this->httpClient->request('GET', $videoUrl, ['sink' => $this->baseDir.$localVideo]);
        }
        return '/'.$localVideo;
    }

    protected function getSourceId($sourceIds,$username){
        if(isset($sourceIds[$username])) {
            $sourceId = $sourceIds[$username];
        }else{
            $source = new \App\Source();
            $source->personId   = 0;
            $source->active     = 1;
            $source->type       = 'instagram';
            $source->code       = $username;

            $source->save();

            $sourceId = $source->id;
        }

        return $sourceId;
    }

    protected function findStored($userStories){
        $usernames  = $ids = [];

        foreach ($userStories as $userStory) {
            $owner      = $userStory->getOwner();
            $stories    = $userStory->getStories();

            $usernames[]    = $owner->getUsername();
            foreach ($stories as $story) {
                $ids[] = $story->getId();
            }
        }

        $localStories = DB::table('stories')
                        ->whereIn('storyId', $ids)
                        ->pluck('storyId')->all();

        $sourceIds  = DB::table('sources')
                        ->where('type', 'instagram')
                        ->whereIn('code', array_unique($usernames))
                        ->pluck( 'id','code')->all();

        return [$localStories,$sourceIds];
    }
}
