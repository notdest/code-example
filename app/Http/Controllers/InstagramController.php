<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\InstagramSessionExpired;


class InstagramController extends Controller
{

    public function edit(){

        $config = new \App\Configs\Instagram();
        return view('instagram.edit',[
            'config'    => $config,
        ]);
    }

    public function save(Request $request){
        $config = new \App\Configs\Instagram();

        $fields = $request->except('_token');
        $validator  = \Validator::make($fields,[
            'enabled'       => 'boolean',
            'emails'        => ['required', function ($attribute, $value, $fail)use($config) {
                $addresses  = explode(',',$value);

                foreach ($addresses as $address){
                    $parts  = explode('|',$address);
                    if(!isset($parts[1])){
                        $fail('«'.$config->fieldName($attribute).'» должен быть вида «Имя|email,Имя2|email2»');
                    }
                }
            }],
        ]);

        $config->apply($fields);

        if($config->enabled > 0){
            $config->errors = 0;
        }

        $success    = false;
        if($validator->passes()){
            $success = $config->save();
        }

        $config = new \App\Configs\Instagram();
        return view('instagram.edit',[
            'config'    => $config,
            'success'   => $success,
            'errors'    => $validator->errors(),
        ]);

    }

    public function checkEmail(){
        $config = new \App\Configs\Instagram();
        $emails = $config->getEmails();
        Mail::to( $emails )->send(new InstagramSessionExpired());
        return back();
    }

}
