<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InstagramController extends Controller
{

    public function edit(){

        $config = new \App\Configs\Instagram();
        return view('instagram.edit',[
            'config'    => $config,
        ]);
    }

    public function save(Request $request){
        $fields = $request->except('_token');
        $validator  = \Validator::make($fields,[
            'enabled'       => 'boolean',
        ]);

        $config = new \App\Configs\Instagram();
        $config->apply($fields);

        $success    = false;
        if($validator->passes()){
            $success = $config->save();
        }

        $config = new \App\Configs\Instagram();
        return view('instagram.edit',[
            'config'    => $config,
            'success'   => $success,
        ]);

    }

}
