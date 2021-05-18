<?php

namespace App\Http\Controllers;

use App\RssSource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RssSourceController extends Controller
{
    public function index(){
        $sources    = RssSource::paginate(50);

        $statistic  = DB::table('rss_imported')
                        ->select(DB::raw('`source_id`, COUNT(`source_id`) AS `cnt`'))
                        ->groupBy('source_id')
                        ->pluck('cnt','source_id')->toArray();

        return view('rssSources.index',[
            'sources'   => $sources,
            'statistic' => $statistic,
        ]);
    }

    public function edit(Request $request){
        return view('rssSources.edit',[
            'source'    => RssSource::findOrFail((int) $request->id),
            'success'   => $request->session()->get('success', false),
        ]);
    }


    public function save(Request $request){
        $source     = RssSource::findOrFail((int) $request->id);

        $fields     = $request->except('_token');
        $source->fill($fields);

        $validator  = \Validator::make($fields,[
            'name'      => 'required',
            'link'      => ['required','active_url',Rule::unique('rss_sources')->ignore($source->id)],
            'streams'   => 'required',
            'active'    => 'boolean',
        ]);

        $success = false;
        if($validator->passes()){
            $source->stream = 0;
            foreach ($fields['streams'] as $stream) {
                $source->stream = $source->stream | $stream;
            }

            $success = $source->save();
        }

        return view('rssSources.edit',[
            'source'    => $source,
            'errors'    => $validator->errors(),
            'success'   => $success,
        ]);
    }

    public function create(){
        return view('rssSources.create',[
            'source'    => new RssSource(),
        ]);
    }

    public function store(Request $request){
        $source     = new RssSource();

        $fields = $request->except('_token');
        $source->fill($fields);

        $validator  = \Validator::make($fields,[
            'name'      => 'required',
            'link'      => 'required|active_url|unique:App\RssSource,link',
            'streams'   => 'required',
            'active'    => 'boolean',
        ]);

        if($validator->passes()){
            $source->stream = 0;
            foreach ($fields['streams'] as $stream) {
                $source->stream = $source->stream | $stream;
            }

            $source->save();
            return redirect('rss-sources/edit/'.$source->id)->with('success', true);
        }else{
            return view('rssSources.create',[
                'source'    => $source,
                'errors'    => $validator->errors(),
            ]);
        }
    }
}
