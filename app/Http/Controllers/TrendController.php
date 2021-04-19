<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Trend;

class TrendController extends Controller
{
    public function index(Request $request){

        $search     = new \stdClass();
        $search->feed       = (int)($request->feed      ?? Trend::FEED_RU);
        $search->from       =       $request->from      ?? date('Y-m-d 00:00:00',time()-7*3600*24);
        $search->to         =       $request->to        ?? date('Y-m-d 23:59:59');
        $search->sorting    = (int)($request->sorting   ?? Trend::SORTING_DATE);

        $db     = Trend::where('pubDate','>=',$search->from )
                       ->where('pubDate','<=',$search->to );

        if($search->feed > 0){
            $db     = $db->where('feed','=',$search->feed );
        }

        if($search->sorting === Trend::SORTING_TRAFFIC){
            $db     = $db->orderBy('traffic', 'desc');
        }else{
            $db     = $db->orderBy('pubDate', 'desc');
        }

        $trends     = $db->take(50)->get();

        return view('trends.index',[
            'trends'    => $trends,
            'search'    => $search,
        ]);
    }
}
