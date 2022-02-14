<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LiveinternetController extends Controller
{
    public function index(Request $request){
        $tab    = $request->tab     ?? 'zen';
        $day    = $request->date    ?? '-1 day';
        if(!in_array($tab,['zen','social','yandex','ru', 's_googl', 'n_y', 'n_g', 'android_google'])){
            $tab    = 'zen';
        }

        $date       = strtotime($day);
        $yesterday  = DB::table('liveinternet') ->where('day',date("Y-m-d",$date))
                                                ->where('type',$tab)
                                                ->orderBy('views','desc')
                                                ->limit(50)
                                                ->pluck('views','site');

        if(!count($yesterday)){     //  Ночью ещё не спарсили
            $date       = strtotime('-1 day',$date);
            $yesterday  = DB::table('liveinternet') ->where('day',date("Y-m-d",$date))
                                                    ->where('type',$tab)
                                                    ->orderBy('views','desc')
                                                    ->limit(50)
                                                    ->pluck('views','site');
        }

        $before     = DB::table('liveinternet') ->where('day',date("Y-m-d",strtotime('-1 day',$date)))
                                                ->where('type',$tab)
                                                ->pluck('views','site');


        $lastWeek   = DB::table('liveinternet') ->select(DB::raw('`site`, SUM(`views`) AS `views`, COUNT(`views`) AS `count`'))
                                                ->where('type',$tab)
                                                ->where('day','<=',date("Y-m-d",$date))
                                                ->where('day','>=',date("Y-m-d",strtotime('-6 day',$date)))
                                                ->groupBy('site')
                                                ->orderBy('views','desc')
                                                ->limit(50)->get();

        $sites          = $lastWeek->pluck('site');
        $previousWeek   = DB::table('liveinternet') ->select(DB::raw('`site`, SUM(`views`) AS `views`, COUNT(`views`) AS `count`'))
                                                    ->where('type',$tab)
                                                    ->whereIn('site',$sites)
                                                    ->where('day','<=',date("Y-m-d",strtotime('-7 day',$date)))
                                                    ->where('day','>=',date("Y-m-d",strtotime('-13 day',$date)))
                                                    ->groupBy('site')->get();


        $lastMonth  = DB::table('liveinternet') ->select(DB::raw('`site`, SUM(`views`) AS `views`, COUNT(`views`) AS `count`'))
                                                ->where('type',$tab)
                                                ->where('day','<=',date("Y-m-d",$date))
                                                ->where('day','>=',date("Y-m-d",strtotime('-29 day',$date)))
                                                ->groupBy('site')
                                                ->orderBy('views','desc')
                                                ->limit(50)->get();

        $sites          = $lastWeek->pluck('site');
        $previousMonth  = DB::table('liveinternet') ->select(DB::raw('`site`, SUM(`views`) AS `views`, COUNT(`views`) AS `count`'))
                                                    ->where('type',$tab)
                                                    ->whereIn('site',$sites)
                                                    ->where('day','<=',date("Y-m-d",strtotime('-30 day',$date)))
                                                    ->where('day','>=',date("Y-m-d",strtotime('-59 day',$date)))
                                                    ->groupBy('site')->get();

        return view('liveinternet.index',[
            'tab'           => $tab,
            'date'          => $date,

            'yesterday'     => $yesterday,
            'before'        => $before,

            'lastWeek'      => $lastWeek,
            'previousWeek'  => $previousWeek,

            'lastMonth'     => $lastMonth,
            'previousMonth' => $previousMonth,
        ]);
    }
}
