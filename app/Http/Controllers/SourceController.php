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

    public function apiIndex(Request $request){
        $search         = new \stdClass();
        $search->person = intval($request->person  ?? 0);
        $search->active = intval($request->active  ?? -1);
        $search->code   = preg_replace('/[^[:alnum:]._]/','',$request->code    ?? '');

        $db     = Source::with('person');
        if($search->person > 0){
            $db = $db->where('personId',$search->person);
        }

        if($search->active > -1){
            $db = $db->where('active',$search->active);
        }else{
            $db = $db->orderBy('active','desc');
        }

        if(strlen($search->code)>0){
            $db = $db->where('code','like', "%{$search->code}%");
        }
        $db = $db->orderBy('id','desc');                                    // важен порядок вызова сортировок


        $paginator  = $db->paginate(100);

        $items      = $paginator->items();
        $sources    = [];
        foreach ($items as $item) {
            $source                 = new \stdClass();
            $source->id             = $item->id;
            $source->code           = $item->code;
            $source->active         = $item->active;
            $source->personId       = $item->personId;
            $source->personName     = $item->person->name;

            $sources[]  = $source;
        }

        return response()->json([
            'lastPage'  => $paginator->lastPage(),
            'sources'   => $sources,
        ]);
    }

    public function apiView(Request $request){
        $source     = Source::with('person')->findOrFail((int) $request->id);
        $source     = $source->toArray();

        $source['personName']   = $source['person']['name'];
        unset($source['userId'],$source['person']);
        return response()->json($source);
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

        $validator  = $this->saveValidator($fields,$source->id);

        $success = false;
        if($validator->passes()){
            if(strlen($fields['new_person']) > 0){              // Одновременное добавление персоны, только в веб-версии
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

    public function apiSave(Request $request){
        $source         = Source::findOrFail((int) $request->id);
        $fields         = $request->except('_token');
        $fields['type'] = 'instagram';                      // в API это пока не нужно
        $source->fill($fields);

        $validator  = $this->saveValidator($fields,$source->id);

        $success = false;
        if($validator->passes()){
            $source->personId   = (int) $fields['person'];
            $success            = $source->save();
        }

        return response()->json([
            'success'   => $success,
            'source'    => $source,
            'errors'    => $validator->errors(),
        ]);
    }

    private function saveValidator($fields,$id){
        $validator  = \Validator::make($fields,[
            'type'       => ['required',Rule::in(array_keys(Source::$types))],
            'code'       => ['required',
                'not_regex:/[\/?]/',
                Rule::unique('sources')->where(function ($query)use($fields){
                    return $query->where('type', $fields['type']);
                })->ignore($id)],
            'new_person' => 'unique:App\Person,name',
            'person'     => 'required_without:new_person',
            'active'     => 'boolean',
        ]);

        return $validator;
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
        $fields     = $request->except('_token');
        $source->fill($fields);

        $validator  = $this->storeValidator($fields);

        if($validator->passes()){
            if(strlen($fields['new_person']) > 0){              // Одновременное добавление персоны, только в веб-версии
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

    public function apiStore(Request $request){
        $source         = new Source();
        $fields         = $request->except('_token');
        $fields['type'] = 'instagram';                      // в API это пока не нужно
        $source->fill($fields);

        $validator  = $this->storeValidator($fields);
        if($validator->passes()){
            $source->personId = (int) $fields['person'];
            $source->save();
            return response()->json([
                'success'   => true,
                'source'    => $source,
            ]);
        }else{
            return response()->json([
                'success'   => false,
                'source'    => $source,
                'errors'    => $validator->errors(),
            ]);
        }
    }

    private function storeValidator($fields){
        $validator  = \Validator::make($fields,[
            'type'       => ['required',Rule::in(array_keys(Source::$types))],
            'code'       => ['required',
                'not_regex:/[\/?]/',
                Rule::unique('sources')->where(function ($query)use($fields){
                    return $query->where('type', $fields['type']);
                })],
            'new_person' => 'unique:App\Person,name',
            'person'     => 'required_without:new_person',
            'active'     => 'boolean',
        ]);

        return $validator;
    }
}
