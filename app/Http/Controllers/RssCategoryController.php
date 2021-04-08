<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\RssCategory;

class RssCategoryController extends Controller
{

    public function index(){

        $categories = RssCategory::with('source')->orderBy('sourceId','desc')->orderBy('id','desc')->paginate(20);

        $articles   = \App\Article::with('source')
                                  ->whereRaw('LENGTH(`unknown_categories`) > 0')
                                  ->orderBy('id','desc')
                                  ->limit(20)->get();

        $unknowns   = [];                                           // получаем неопознанные категории из статей
        $sources    = [];
        foreach ($articles as $article) {
            $ucats  = explode(', ',$article->unknown_categories);
            foreach ($ucats as $ucat) {
                $unknowns[$article->source->id][]   = trim($ucat);
                $sources[ $article->source->id ]    = $article->source->name;
            }
        }

        foreach ($unknowns as $k => $unknown) {
            $unknowns[$k]  = array_unique($unknown);
        }

        return view('rssCategories.index',[
            'categories'    => $categories,
            'articles'      => $articles,
            'unknowns'      => $unknowns,
            'sources'       => $sources,
        ]);
    }


    public function delete(Request $request){
        $id = (int) $request->id;

        if($id){
            $person = RssCategory::find($id);
            $person->delete();
        }

        return back();
    }

    public function write(Request $request){
        $ourCategory    = $request->ourCategory;
        $source         = $request->source;
        $theirCategory  = $request->theirCategory;

        $insertion = [];                                                        // Сохраняем новые категории
        $removable = [];
        foreach ($ourCategory as $k => $category) {
            if( $category == -1 ){
                $removable[$source[$k]][trim($theirCategory[$k])]   = 1;        // попутно запоминаем, какие удалить
            }elseif ($category != 0){
                $insertion[] = [
                    'sourceId'  => (int) $source[$k],
                    'name'      => trim($theirCategory[$k]),
                    'category'  => (int) $category,
                ];
            }
        }
        if(count($insertion)){
            RssCategory::insert($insertion);
        }


        $deleter = function ($source,$categories) use($removable){              // делаем функцию для удаления битых категорий
            $unknown        = [];
            foreach ($categories as $category) {
                $category = trim($category);
                if(!isset($removable[$source][$category])){
                    $unknown[]  = $category;
                }
            }
            return $unknown;
        };


        $classifier     = RssCategory::getClassifier();                         // Применяем фильтр к статьям

        \App\Article::whereRaw('LENGTH(`unknown_categories`) > 0')
            ->chunkById(200, function ($articles) use ($classifier,$deleter){ // специальный метод, чтобы перебирать модели изменяя их
                foreach ($articles as $article) {

                    $unknownFirst   = explode(', ',$article->unknown_categories);

                    list($additional,$unknown)  = $classifier(  $article->source_id,$unknownFirst);
                    $unknown                    = $deleter(     $article->source_id,$unknown);


                    if(count($unknownFirst) > count($unknown)){
                        $article->categories            = $article->categories | $additional;
                        $article->unknown_categories    = implode(', ',$unknown);
                        $article->save();
                    }
                }
            });


        return back();
    }
}
