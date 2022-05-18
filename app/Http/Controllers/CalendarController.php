<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\{Event,RegularEvent,WeekEvent};

class CalendarController extends Controller
{
    public function index(Request $request){
        $search = $this->getSearch($request);
        $events = $this->getEvents($search);

        $weekEvents = $this->getWeekEvents($search);
        $weekEvents = $weekEvents->groupBy(function ($item, $key) {
            return trim($item['title']);
        })->toArray();

        foreach ($events as $event) {
            $key    = trim($event->title);
            if(isset($weekEvents[$key])){
                foreach ($weekEvents[$key] as $weekEvent) {
                    if( $weekEvent['category'] === $event->category && $weekEvent['start'] === $event->start ){
                        $event->weekEvent = $weekEvent['id'];
                    }
                }
            }
        }

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

    public function apiIndex(Request $request){
        $search = $this->getSearch($request);
        $events = $this->getEvents($search);

        $result = [];
        foreach ($events as $event) {
            $result[]   = [ 'id'    => $event->id,      'title' => $event->title, 'category' => $event->category,
                            'start' => $event->start,   'end'   => $event->end  ];
        }

        return response()->json(['events'    => $result, 'categories' => Event::$categories]);
    }



    private function getSearch($request){
        $search = new \stdClass();
        $search->category   = (int)($request->category  ?? 0);
        $search->year       = (int)($request->year      ?? date('Y'));
        $search->month      = (int)($request->month     ?? date('n'));

        return $search;
    }

    private function getEvents($search){
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

        return $events;
    }

    private function getWeekEvents($search){
        $computeTime    = strtotime("{$search->year}-{$search->month}-05 00:00:00");
        $db = WeekEvent::where('end','>=',date('Y-m-01 00:00:00',$computeTime) );
        $db = $db->where(  'end','<=',date('Y-m-t 23:59:59' ,$computeTime) );

        if($search->category > 0){
            $db = $db->where('category', $search->category);
        }

        $db = $db->orderBy('start', 'asc');
        $events = $db->take(1000)->get();

        return $events;
    }


    public function week(Request $request){
        $search = new \stdClass();
        $search->year       = (int)($request->year      ?? date('Y'));
        $search->week       = (int)($request->week      ?? date("W", strtotime('+3 day')));

        $dto    = new \DateTime();
        $from   = $dto->setISODate($search->year, $search->week)->format('Y-m-d 00:00:00');
        $to     = $dto->modify('+6 days')->format('Y-m-d 23:59:59');

        $db = WeekEvent::where('end','>=',$from );
        $db = $db->where(  'end','<=',$to);

        $db = $db->orderBy('start', 'asc');
        $events = $db->take(1000)->get();

        $days   = [];
        foreach ($events as $event) {
            $days[] = date('j',strtotime($event->start));
        }
        $days   = array_unique($days);

        return view('calendar.week',[
            'events'    => $events,
            'days'      => $days,
            'search'    => $search,
            'weekStart' => $from,
        ]);
    }

    public function addWeekEvent(Request $request){
        $id         = intval($request->id);
        $regular    = $request->regular === 'true';

        if($regular){
            $event  = RegularEvent::find($id);
        }else{
            $event  = Event::find($id);
        }

        $weekEvent  = WeekEvent:: where('title', trim($event->title))
                                ->where('category', $event->category)
                                ->where('start', $event->start)->first();

        if(!$weekEvent){
            $weekEvent  = WeekEvent::create([
                'title'     => trim($event->title),
                'category'  => $event->category,
                'start'     => $event->start,
                'end'       => $event->end,
            ]);
        }

        if($request->ajax()){
            return response()->json([ 'id'  => $weekEvent->id ]);
        }else{
            return back();
        }
    }

    public function deleteWeekEvent(Request $request){
        $id     = intval($request->id);

        if($id){
            $event  = WeekEvent::find($id);
            $event->delete();
        }

        if($request->ajax()){
            return response()->json([ 'success'  => true ]);
        }else{
            return back();
        }
    }
}
