<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PersonController extends Controller
{

    public function index(){
        $persons = \App\Person::paginate(15);

        return view('persons.index',[
            'persons'  => $persons
        ]);
    }

    public function delete(Request $request){
        $id = (int) $request->id;

        if($id){
            $person = \App\Person::find($id);
            $person->delete();
        }

        return back();
    }
}
