<?php

namespace App\Http\Controllers;

use App\RegularEvent;
use Illuminate\Http\Request;

class RegularEventController extends Controller
{

    public function index(Request $request){
        $db     = RegularEvent::orderBy('start', 'asc');
        $events = $db->get();

        return view('regularEvents.index',[
            'events'    => $events
        ]);
    }

    public function tsv(Request $request){
        $fields     = $request->except('_token');

        $validator  = \Validator::make($fields,[
            'category'  => 'required|integer|min:1',
            'tsv'       => 'required'
        ]);

        if(!$validator->passes()){
            return redirect('/regular-events/')->with('errors',  $validator->errors());
        }

        $lines  = explode("\n",$fields['tsv']);

        foreach ($lines as $line) {
            $cells  = explode("\t",$line);

            if(count($cells)===2){
                $event  = new RegularEvent();
                $event->start       = $cells[0];
                $event->end         = $cells[0];
                $event->category    = intval($fields['category']);
                $event->title       = (strlen($cells[1])>255) ? mb_substr($cells[1],0,240).'...': $cells[1];

                $event->save();
            }
        }

        return redirect('/regular-events/')->with('success', true);
    }


    public function delete(Request $request){
        $id = (int) $request->id;

        if($id){
            $event = RegularEvent::find($id);
            $event->delete();
        }

        return back();
    }

    public function save(Request $request){
        $fields     = $request->except('_token');

        $validator  = \Validator::make($fields,[
            'category'  => 'required|integer|min:1',
            'title'     => 'required',
            'start'     => 'required',
            'end'       => 'required',
        ]);

        if(!$validator->passes()){
            return redirect('/regular-events/')->with('errors',  $validator->errors());
        }

        $event  = new RegularEvent();
        $event->start       = $fields['start'];
        $event->end         = $fields['end'];
        $event->category    = intval($fields['category']);
        $event->title       = $fields['title'];

        $success = $event->save();

        return redirect('/regular-events/')->with('success', $success);
    }
}
