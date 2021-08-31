<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Event;

class CalendarController extends Controller
{
    public function index(Request $request){
        $search = new \stdClass();
        $search->category   = (int)($request->category ?? 0);

        $db = Event::where('end','>=',date('Y-m-d 00:00:00',strtotime('-1 day')) );

        if($search->category > 0){
            $db = $db->where('category', $search->category);
        }

        $db = $db->orderBy('start', 'asc');
        $events = $db->take(1000)->get();

        return view('calendar.index',[
            'events'    => $events,
            'search'    => $search,
        ]);
    }

}
