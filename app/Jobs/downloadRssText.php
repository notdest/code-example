<?php

namespace App\Jobs;

use App\RssSource;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class downloadRssText implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $debugId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $debugId = 0)
    {
        $this->debugId   = $debugId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $db     = \App\Article::with('source')
                              ->where('pub_date','>=',date("Y-m-d 00:00:00",time()-(7*24*3600)))
                              ->where('original_text','');

        if($this->debugId){
            $db     = $db->where('source_id',$this->debugId);
        }else{
            $ids    = DB::table('rss_sources')->where('text_adapter','<>', '')->get()->pluck('id');
            $db     = $db->whereIn('source_id',$ids);
        }

        $articles   = $db->get();




        foreach ($articles as $article) {
            if($this->debugId){
                echo $article->link."\n";
            }

            $adapter    = $this->getAdapter($article->source->text_adapter);
            $text       = $adapter->download($article->link);

            if($this->debugId){
                print_r($text);
                echo "\n\n\n";
            }else{
                $article->original_text    = $text;
                $article->save();
            }

            sleep(1);
        }
    }


    protected function getAdapter($name){
        static $adapters = [];
        static $client;

        if(!$client){
            $client     = new \GuzzleHttp\Client();
        }

        if(isset($adapters[$name])){
            return $adapters[$name];
        }else{
            $class      = '\App\Jobs\rss_text_adapters\\'.$name;
            $adapter    = new  $class($client);
            $adapters[$name]    = $adapter;
            return $adapter;
        }
    }

}
