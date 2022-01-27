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

    public function apiIndex(Request $request){
        $name   = $request->name;
        $db     = Person::orderBy('hidden')
                        ->orderBy('id','desc');

        if(strlen($name)>0){
            $name   = str_replace(['"',"'","\\"],'',$name);
            $db     = $db->where('name', 'like', "$name%");
        }

        $persons    = $db->paginate(100);
        return response()->json([
            'lastPage'  => $persons->lastPage(),
            'persons'   => $persons->items(),
        ]);
    }

    public function apiView(Request $request){
        return response()->json(Person::findOrFail((int) $request->id));
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
        [$person, $validator]   = $this->saveValidator($request);

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

    public function apiSave(Request $request){
        [$person, $validator]   = $this->saveValidator($request);

        $success = false;
        if($validator->passes()){
            $success = $person->save();
        }

        return response()->json([
            'success'   => $success,
            'person'    => $person,
            'errors'    => $validator->errors(),
        ]);
    }

    private function saveValidator($request){
        $person     = Person::findOrFail((int) $request->id);

        $fields     = $request->except('_token');
        $person->fill($fields);

        $validator  = \Validator::make($fields,[
            'name'      => ['required',Rule::unique('persons')->ignore($person->id)],
            'hidden'    => 'boolean',
        ]);

        return [$person, $validator];
    }

    public function create(){
        return view('persons.create',[
            'person'    => new Person(),
        ]);
    }

    public function store(Request $request){
        [$person,$validator]    = $this->storeValidator($request);

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

    public function apiStore(Request $request){
        [$person,$validator]    = $this->storeValidator($request);

        if($validator->passes()){
            $person->save();
            return response()->json([
                'success'   => true,
                'person'    => $person,
            ]);
        }else{
            return response()->json([
                'success'   => false,
                'person'    => $person,
                'errors'    => $validator->errors(),
            ]);
        }
    }

    private function storeValidator($request){
        $person     = new Person();

        $fields = $request->except('_token');
        $person->fill($fields);

        $validator  = \Validator::make($fields,[
            'name'      => 'required|unique:App\Person,name',
            'hidden'    => 'boolean',
        ]);

        return  [$person,$validator];
    }
}
