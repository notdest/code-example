<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\{Event,RegularEvent};

class CalendarController extends Controller
{
    public function index(Request $request){
        $search = new \stdClass();
        $search->category   = (int)($request->category  ?? 0);
        $search->year       = (int)($request->year      ?? date('Y'));
        $search->month      = (int)($request->month     ?? date('n'));

        $computeTime    = strtotime("{$search->year}-{$search->month}-05 00:00:00");
        $db = Event::where('end','>=',date('Y-m-01 00:00:00',$computeTime) );
        $db = $db->where(  'end','<=',date('Y-m-t 23:59:59' ,$computeTime) );

        if($search->category > 0){
            $db = $db->where('category', $search->category);
        }

        $db = $db->orderBy('start', 'asc');
        $events = $db->take(1000)->get();
        $events = $events->concat(RegularEvent::getMonth($search));
        $events = $events->sortBy(function ($event, $key) {
            return strtotime($event->start);
        });

        $days   = [];
        foreach ($events as $event) {
            $days[] = date('j',strtotime($event->start));
        }
        $days   = array_unique($days);
        sort($days);

        return view('calendar.index',[
            'events'    => $events,
            'days'      => $days,
            'search'    => $search,
        ]);
    }

}
