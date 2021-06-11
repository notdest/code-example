<?php

namespace App\Http\Controllers;

use App\Source;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SourceController extends Controller
{

    public function index(){
        $sources = Source::with('person')
                         ->orderBy('active','desc')
                         ->orderBy('id','desc')
                         ->paginate(30);

        return view('sources.index',[
            'sources'  => $sources
        ]);
    }

    public function activate(Request $request){
        $id = (int) $request->id;

        if($id){
            $sources         = Source::find($id);
            $sources->active = 1;
            $sources->save();
        }

        return back();
    }

    public function deactivate(Request $request){
        $id = (int) $request->id;

        if($id){
            $sources         = Source::find($id);
            $sources->active = 0;
            $sources->save();
        }

        return back();
    }

    public function edit(Request $request){
        $persons    = \App\Person::orderBy('name')->get();

        return view('sources.edit',[
            'source'    => Source::findOrFail((int) $request->id),
            'persons'   => $persons,
            'success'   => $request->session()->get('success', false),
        ]);
    }

    public function save(Request $request){
        $source     = Source::findOrFail((int) $request->id);

        $fields     = $request->except('_token');
        $source->fill($fields);

        $validator  = \Validator::make($fields,[
            'type'       => ['required',Rule::in(array_keys(Source::$types))],
            'code'       => ['required',
                                'not_regex:/[\/?]/',
                                Rule::unique('sources')->where(function ($query)use($fields){
                                    return $query->where('type', $fields['type']);
                                })->ignore($source->id)],
            'new_person' => 'unique:App\Person,name',
            'active'     => 'boolean',
        ]);

        $success = false;
        if($validator->passes()){
            if(strlen($fields['new_person']) > 0){
                $person         = new \App\Person();
                $person->name   = $fields['new_person'];
                $person->hidden = 0;
                $person->save();
                $source->personId = $person->id;
            }else{
                $source->personId = (int) $fields['person'];
            }

            $success = $source->save();
        }

        $persons    = \App\Person::orderBy('name')->get();
        return view('sources.edit',[
            'source'    => $source,
            'persons'   => $persons,
            'errors'    => $validator->errors(),
            'success'   => $success,
        ]);
    }

    public function create(){
        $persons    = \App\Person::orderBy('name')->get();
        return view('sources.create',[
            'source'    => new Source(),
            'persons'   => $persons,
        ]);
    }

    public function store(Request $request){
        $source     = new Source();

        $fields = $request->except('_token');
        $source->fill($fields);

        $validator  = \Validator::make($fields,[
            'type'       => ['required',Rule::in(array_keys(Source::$types))],
            'code'       => ['required',
                                'not_regex:/[\/?]/',
                                Rule::unique('sources')->where(function ($query)use($fields){
                                    return $query->where('type', $fields['type']);
                                })],
            'new_person' => 'unique:App\Person,name',
            'active'     => 'boolean',
        ]);

        if($validator->passes()){
            if(strlen($fields['new_person']) > 0){
                $person         = new \App\Person();
                $person->name   = $fields['new_person'];
                $person->hidden = 0;
                $person->save();
                $source->personId = $person->id;
            }else{
                $source->personId = (int) $fields['person'];
            }

            $source->save();
            return redirect('sources/edit/'.$source->id)->with('success', true);
        }else{
            $persons    = \App\Person::orderBy('name')->get();
            return view('sources.create',[
                'source'    => $source,
                'persons'   => $persons,
                'errors'    => $validator->errors(),
            ]);
        }
    }
}
