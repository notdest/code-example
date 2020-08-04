<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{

    public function index(Request $request){

        $search     = new \stdClass();
        $search->person = $request->person ?? 0;
        $search->source = $request->source ?? 0;
        $search->from   = $request->from    ?? date('Y-m-d 00:00:00');
        $search->to     = $request->to      ?? date('Y-m-d 23:59:59');

        $persons    = DB::select("SELECT * FROM `persons` LIMIT 20");

        $posts  = DB::table('posts')
                    ->join('sources', 'posts.sourceId', '=', 'sources.id')
                    ->join('persons', 'sources.personId', '=', 'persons.id')
                    ->where('createdTime','>=',$search->from )
                    ->where('createdTime','<=',$search->to );

        if($search->person){
            $posts  = $posts->where('sources.personId','=',$search->person);
        }

        if($search->source){
            $posts  = $posts->where('sources.type','=','facebook');
        }

        $posts  = $posts->paginate(15);


        return view('posts.index',[
            'posts'     => $posts,
            'persons'   => $persons,
            'search'    => $search,
        ]);
    }
}
