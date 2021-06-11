<?php

namespace App\Http\Controllers;

use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use App\Person;

class PersonController extends Controller
{

    public function index(){
        $persons = Person::orderBy('hidden')
                              ->orderBy('id','desc')
                              ->paginate(30);

        return view('persons.index',[
            'persons'  => $persons
        ]);
    }


    public function hide(Request $request){
        $id = (int) $request->id;

        if($id){
            $person         = Person::find($id);
            $person->hidden = 1;
            $person->save();
        }

        return back();
    }

    public function show(Request $request){
        $id = (int) $request->id;

        if($id){
            $person         = Person::find($id);
            $person->hidden = 0;
            $person->save();
        }

        return back();
    }

    public function edit(Request $request){
        return view('persons.edit',[
            'person'    => Person::findOrFail((int) $request->id),
            'success'   => $request->session()->get('success', false),
        ]);
    }

    public function save(Request $request){
        $person     = Person::findOrFail((int) $request->id);

        $fields     = $request->except('_token');
        $person->fill($fields);

        $validator  = \Validator::make($fields,[
            'name'      => ['required',Rule::unique('persons')->ignore($person->id)],
            'hidden'    => 'boolean',
        ]);

        $success = false;
        if($validator->passes()){
            $success = $person->save();
        }

        return view('persons.edit',[
            'person'    => $person,
            'errors'    => $validator->errors(),
            'success'   => $success,
        ]);
    }

    public function create(){
        return view('persons.create',[
            'person'    => new Person(),
        ]);
    }

    public function store(Request $request){
        $person     = new Person();

        $fields = $request->except('_token');
        $person->fill($fields);

        $validator  = \Validator::make($fields,[
            'name'      => 'required|unique:App\Person,name',
            'hidden'    => 'boolean',
        ]);

        if($validator->passes()){
            $person->save();
            return redirect('persons/edit/'.$person->id)->with('success', true);
        }else{
            return view('persons.create',[
                'person'    => $person,
                'errors'    => $validator->errors(),
            ]);
        }
    }
}
