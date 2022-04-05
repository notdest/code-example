<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class translateRss implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
    public function handle(){
        $limit  = 50;

        do {
            $articles = \App\Article::where('translate', '>', 0)->take($limit)->get();
            if (count($articles) > 0) {
                $contents = [];
                foreach ($articles as $article) {
                    $contents[] = $article->foreign_title;
                }

                $translations = \YandexTranslate::batch($contents) ;

                if (count($translations) !== count($articles)) {
                    throw new \RuntimeException("the number of translations does not match the number of articles");
                }

                foreach ($translations as $k => $translation) {
                    $article = $articles[$k];   // я не нашел способа однозначно связать перевод с оригиналом
                    $article->translate = 0;
                    $article->title = $translation;
                    $article->save();
                }
            }
        }while(count($articles)>= $limit);
    }
}
