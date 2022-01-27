<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{

    public function index(Request $request){
        $search     = $this->getSearch($request);
        $persons    = DB::select("SELECT * FROM `persons` ");
        $posts      = $this->getPosts($search);
        $config     = new \App\Configs\Instagram();

        return view('posts.index',[
            'posts'     => $posts,
            'persons'   => $persons,
            'search'    => $search,
            'enabled'   => $config->enabled,
        ]);
    }


    public function apiIndex(Request $request){
        $search     = $this->getSearch($request);
        $paginator  = $this->getPosts($search);
        $persons    = DB::select("
            SELECT DISTINCT
                `personId`,
                `name`
            FROM `sources`
            LEFT JOIN `persons` ON `sources`.`personId`=`persons`.`id`
            WHERE `type` = 'instagram' AND `active` > 0
            ORDER BY `name` ;");

        $items  = $paginator->items();
        $posts  = [];
        foreach ($items as $item) {
            $post   = new \stdClass();
            $post->postId       = $item->postId;
            $post->image        = url($item->image);
            $post->text         = $item->text;
            $post->createdTime  = $item->createdTime;
            $post->sourceId     = $item->sourceId;
            $post->sourceCode   = $item->code;
            $post->personId     = $item->personId;
            $post->personName   = $item->name;

            $posts[]    = $post;
        }

        return response()->json([
            'lastPage'  => $paginator->lastPage(),
            'posts'     => $posts,
            'persons'   => $persons,
        ]);
    }


    private function getSearch($request){
        $search         = new \stdClass();
        $search->person = $request->person  ?? 0;
        $search->source = $request->source  ?? 0;
        $search->from   = $request->from    ?? date('Y-m-d 00:00:00');
        $search->to     = $request->to      ?? date('Y-m-d 23:59:59');

        return $search;
    }

    private function getPosts($search){
        $posts  = DB::table('posts')
                    ->join('sources', 'posts.sourceId', '=', 'sources.id')
                    ->join('persons', 'sources.personId', '=', 'persons.id')
                    ->where('createdTime','>=',$search->from )
                    ->where('createdTime','<=',$search->to )
                    ->orderBy('createdTime', 'desc');

        if($search->person){
            $posts  = $posts->where('sources.personId','=',$search->person);
        }

        if($search->source){
            $posts  = $posts->where('sources.type','=','facebook');
        }

        $posts  = $posts->paginate(60);
        return $posts;
    }
}
