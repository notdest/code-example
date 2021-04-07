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
        $source = $request->source;

        print_r($source);die();
    }
}
