<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoryController extends Controller
{
    const PHOTO = 1;
    const VIDEO = 2;

    public function index(Request $request){
        $search     = $this->getSearch($request);
        $persons    = DB::select("SELECT * FROM `persons` ORDER BY `name`");
        $stories    = $this->getStories($search);
        $config     = new \App\Configs\Instagram();

        return view('stories.index',[
            'stories'   => $stories,
            'persons'   => $persons,
            'search'    => $search,
            'enabled'   => $config->enableStories,
        ]);
    }

    public function apiIndex(Request $request){
        $search     = $this->getSearch($request);
        $paginator  = $this->getStories($search);
        $persons    = DB::select("
            SELECT DISTINCT
                `personId`,
                `name`
            FROM `sources`
            LEFT JOIN `persons` ON `sources`.`personId`=`persons`.`id`
            WHERE `type` = 'instagram' AND `active` > 0
            ORDER BY `name` ;");


        $items      = $paginator->items();
        $stories    = [];
        foreach ($items as $item) {
            $story  = new \stdClass();
            $story->storyId     = $item->storyId;
            $story->type        = ($item->type === self::PHOTO) ? 'photo':'video';
            $story->image       = url($item->image);
            $story->video       = url($item->video);
            $story->duration    = $item->duration;
            $story->createdTime = $item->createdTime;
            $story->sourceId    = $item->sourceId;
            $story->sourceCode  = $item->code;
            $story->personId    = $item->personId;
            $story->personName  = $item->name;

            $stories[]    = $story;
        }

        return response()->json([
            'lastPage'  => $paginator->lastPage(),
            'stories'   => $stories,
            'persons'   => $persons,
        ]);
    }

    private function getSearch($request){
        $search     = new \stdClass();
        $search->person = $request->person  ?? 0;
        $search->from   = $request->from    ?? date('Y-m-d 00:00:00');
        $search->to     = $request->to      ?? date('Y-m-d 23:59:59');

        return $search;
    }

    private function getStories($search){
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
        return $stories;
    }
}
