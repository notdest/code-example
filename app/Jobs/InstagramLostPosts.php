<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use InstagramScraper\Exception\InstagramNotFoundException;

class InstagramLostPosts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        file_put_contents(\App\Http\Controllers\InstagramController::$latch,date('Y-m-d H:i:s'));

        $this->output("launch on ".date("Y-m-d H:i:s"),true);
        $config     = new \App\Configs\Instagram();
        $instagram  = $config->getClient();

        $sources  = DB::select("SELECT * FROM `sources` WHERE `type`='instagram' AND `active` > 0 ;");

        foreach ($sources as $source) {
            $this->output("\n\nhttps://www.instagram.com/{$source->code}/\n");
            try {
                $posts  =   $instagram->getMedias($source->code);                           // скачиваем 20 постов
            }catch (InstagramNotFoundException $e){
                $this->output("could not download: https://www.instagram.com/{$source->code}/\n");
                continue;
            }catch (\Exception $e){                                     // разок может вылететь
                sleep(15);
                $posts  =   $instagram->getMedias($source->code);
            }

            $ids = [];
            foreach ($posts as $post) {
                $ids[]  = $post->getId();
            }


            $localPosts = DB::table('posts')
                            ->whereIn('numericalId', $ids)
                            ->pluck('numericalId')->all();


            foreach ($posts as $post) {
                if (in_array($post->getId(), $localPosts)) {                        // если уже сохраняли то пропускаем
                    break;
                }

                $this->output(date("Y-m-d H:i:s",$post->getCreatedTime())."\t\t\t".$post->getLink()."\n");
            }
            sleep(1);
        }

        unlink(\App\Http\Controllers\InstagramController::$latch);
    }

    private $debugOutput = false;
    protected function output($text,$first = false){
        if($this->debugOutput){
            echo $text;
        }else{
            if($first){
                file_put_contents('/var/www/storage/app/public/lost_posts.txt',$text);
            }else{
                file_put_contents('/var/www/storage/app/public/lost_posts.txt',$text,FILE_APPEND);
            }
        }
    }
}
