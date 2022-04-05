<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArticleRewriteController extends Controller
{
    public function index(Request $request){
        $search             = new \stdClass();
        $search->translate  = (int) ($request->translate    ?? 1); // влияет только на отображение
        $search->source     =        $request->source       ?? '';

        $sources    = DB::table('articles_rewrite')->select('source')->distinct()->orderBy('source')->pluck('source')->all();
        if(!in_array($search->source,$sources)){
            $search->source = '';
        }

        $db         = \App\ArticleRewrite::orderBy('id', 'desc');

        if(strlen($search->source)>0){
            $db = $db->where('source',$search->source);
        }

        $articles   = $db->paginate(500);

        return view('articlesRewrite.index',[
            'articles'  => $articles,
            'search'    => $search,
            'sources'   => $sources,
        ]);
    }


    public function text(Request $request){
        return view('articlesRewrite.text',[
            'article'   => \App\ArticleRewrite::findOrFail((int) $request->id),
        ]);
    }



    public function translate(Request $request){
        $article    = \App\ArticleRewrite::findOrFail((int) $request->id);

        if((strlen($article->translated_text)>0)&&(strlen($article->title)>0)){
            return [
                'success'           => true,
                'translatedTitle'   => $article->title,
                'translatedText'    => str_replace("\n","<br>\n",$article->translated_text),
            ];
        }

        if(!strlen($article->title) && strlen(trim($article->foreign_title))){
            $article->title     = \YandexTranslate::single($article->foreign_title);
            $article->save();
        }

        if(!strlen($article->translated_text) && strlen(trim($article->original_text))){
            $article->translated_text = \YandexTranslate::large($article->original_text);
            $article->save();
        }

        return [
            'success'           => true,
            'translatedTitle'   => $article->title,
            'translatedText'    => str_replace("\n","<br>\n",$article->translated_text),
        ];
    }


    public function translateTitles(){
        $limit  = 50;

        do {
            $articles = \App\ArticleRewrite::where('title', '')->take($limit)->get();
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
                    $article->title = $translation;
                    $article->save();
                }
            }
        }while(count($articles)>= $limit);

        return back();
    }


    /*  Специальный метод для массового заполнения - записывает статью из присланного JSON
     *  используется совместно с методом parseArticle() исполняемым на компьютере разработчика.
     *  Требует пользоавателя-админа.
     */
    public function saveArticle(Request $request){
        $article    = new \App\ArticleRewrite();

        $fields     = $request->json()->all();
        $article->fill($fields);

        $validator  = \Validator::make($fields,[
            'link'              => 'required|unique:App\ArticleRewrite,link',
            'source'            => 'required',
            'title'             => 'required_without:foreign_title',
            'foreign_title'     => 'required_without:title',
            'original_text'     => 'required_without:translated_text',
            'translated_text'   => 'required_without:original_text',

        ]);

        if($validator->passes()){
            $article->save();
            return "Success";
        }else{
            $errors = $validator->errors();
            return $errors->first();
        }
    }

    /*  Метод для массового заполнения статей, выполняется только на компьютере разработчика.
     *  Сделан, чтобы не заливать одноразовые адаптеры под каждый новый сайт. Вызывается:
     *  curl http://localhost:8787/articles-rewrite/parse-article/?page=https://www.eatthis.com/in-n-out-secret-menu-items/
     */
    public function parseArticle(Request $request){
        return "Forbidden for all\n";                                       // на локальной машине удаляем эту строчку

        $login      = "admin@admin.admin";                                  // Только пользователи с админскими правами
        $password   = "pass";
        $host       = "http://localhost";                                   // для локальной отладки
        //$host       = "https://parser.fppressa.ru";                       // для залива на бой
        $debug      = true;                                                 // если true, то просто выводим собранное

        $page       = $request->page;
        if(!strlen($page)){
            return "no page parameter specified\n";
        }

        $client     = new \GuzzleHttp\Client();

        $article                    = new \StdClass();
        $article->title             = "";
        $article->source            = "";
        $article->foreign_title     = "";
        $article->link              = $page;
        $article->original_text     = "";
        $article->translated_text   = "";

        $response   = $client->get($page);
        if($response->getStatusCode() >= 300){
            return "The specified page gave bad status: ".$response->getStatusCode()."\n";
        }

        try{
            $this->parse($response->getBody(),$article);                    // Здесь парсим весь HTML
        }catch (\Exception $e){
            return $e->getMessage()."\n";
        }

        if($debug){
            print_r($article);
            return "\n\n\n";
        }else{
            $response   = $client->post($host.'/articles-rewrite/save-article/', [
                    'auth'  => [$login, $password],
                    'json'  => $article,
            ]);

            return $response->getBody()."\n";
        }
    }



    private function parse($html,&$article){
        // Пример адаптера для www.psychologytoday.com
        $article->source    = "Psychologytoday.com";

        $pos    = strpos($html,'<div id="block-pt-content"');                   // Обрезаем всё кроме тела статьи
        if($pos === false){
            throw new \Exception('Can\'t found "block-pt-content"');
        }
        $html   = substr($html,$pos);

        $pos    = strpos($html,'<aside class="layout-content-left-rail--first"');
        if($pos === false){
            $pos    = strpos($html,'<div class="self-test__button">');
        }
        if($pos === false){
            $pos    = strpos($html,'<div class="blog-entry-references"');
        }
        if($pos === false){
            throw new \Exception('Can\'t found "layout-content-left-rail--first"');
        }
        $html   = substr($html,0,$pos);

        preg_match_all('~<h1[^>]*>([^<]*)</h1>~',$html,$m);                     // получаем заголовок
        if(!isset($m[1][0])){
            throw new \Exception('Can\'t found H1');
        }
        $article->foreign_title     = trim($m[1][0]);

        $pos    = strpos($html,'</h1>');                                        // обрезаем заголовок
        if($pos === false){
            throw new \Exception('Can\'t found "</h1>"');
        }
        $html   = substr($html,$pos);

        $html   = preg_replace('~<script.*</script>~is','',$html);
        $html   = strip_tags($html);                                            // вытаскиваем текст из месива тэгов
        $html   = html_entity_decode($html);
        $html   = trim($html);
        $html   = preg_replace('~^[  \t]*~m','',$html);
        $html   = preg_replace('~\n{3,}~m',"\n\n",$html);

        $article->original_text = $html;
    }

}
