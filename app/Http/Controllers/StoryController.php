<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoryController extends Controller
{
    const PHOTO = 1;
    const VIDEO = 2;

    public function index(Request $request){
        $search     = new \stdClass();
        $search->person = $request->person  ?? 0;
        $search->from   = $request->from    ?? date('Y-m-d 00:00:00');
        $search->to     = $request->to      ?? date('Y-m-d 23:59:59');

        $persons    = DB::select("SELECT * FROM `persons` ORDER BY `name`");

        $stories    = DB::table('stories')
                    ->join('sources', 'stories.sourceId', '=', 'sources.id')
                    ->join('persons', 'sources.personId', '=', 'persons.id')
                    ->select('stories.*', 'sources.code', 'sources.personId', 'persons.name')
                    ->where('createdTime','>=',$search->from )
                    ->where('createdTime','<=',$search->to )
                    ->orderBy('createdTime', 'desc');

        if($search->person){
            $stories  = $stories->where('sources.personId','=',$search->person);
        }

        $stories  = $stories->paginate(60);

        $config = new \App\Configs\Instagram();
        return view('stories.index',[
            'stories'   => $stories,
            'persons'   => $persons,
            'search'    => $search,
            'enabled'   => $config->enableStories,
        ]);
    }
}
