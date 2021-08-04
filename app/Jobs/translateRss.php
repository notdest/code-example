<?php

namespace App\Jobs;

use Google\Cloud\Translate\V3\TranslationServiceClient;
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
        $translationClient = new TranslationServiceClient([
            'credentials' => config_path('google-translate-credentials.json'),
        ]);
        $parent = TranslationServiceClient::locationName(app('config')['common.translate.projectId'], 'global');


        do {
            $articles = \App\Article::where('translate', '>', 0)->take($limit)->get();
            if (count($articles) > 0) {
                $contents = [];
                foreach ($articles as $article) {
                    $contents[] = $article->foreign_title;
                }

                $translations = $translationClient->translateText($contents, 'ru', $parent)->getTranslations();

                if (count($translations) !== count($articles)) {
                    throw new \RuntimeException("the number of translations does not match the number of articles");
                }

                foreach ($translations as $k => $translation) {
                    $article = $articles[$k];   // я не нашел способа однозначно связать перевод с оригиналом
                    $article->translate = 0;
                    $article->title = html_entity_decode($translation->getTranslatedText(),ENT_QUOTES);
                    $article->save();
                }
            }
        }while(count($articles)>= $limit);

        $translationClient->close();
    }
}
