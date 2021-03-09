<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrendController extends Controller
{
    public function index(Request $request){

        $search     = new \stdClass();
        $search->feed   = $request->feed    ?? 0;
        $search->from   = $request->from    ?? date('Y-m-d 00:00:00',time()-7*3600*24);
        $search->to     = $request->to      ?? date('Y-m-d 23:59:59');

        $db     = \App\Trend::where('pubDate','>=',$search->from )
                            ->where('pubDate','<=',$search->to );

        if($search->feed > 0){
            $db     = $db->where('feed','=',$search->feed );
        }

        $trends = $db->orderBy('pubDate', 'desc')
                     ->paginate(15);

        return view('trends.index',[
            'trends'    => $trends,
            'search'    => $search,
        ]);
    }
}
