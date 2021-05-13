<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\InstagramSessionExpired;
use InstagramScraper\Exception\InstagramException;


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

    public function checkSubscribed(){
        $config     = new \App\Configs\Instagram();

        if ($config->enabled == 0){
            return "Парсер выключен";
        }

        $instagram  = $config->getClient();
        sleep(2);

        $account    = $instagram->getAccount($config->login);
        sleep(1);

        try {
            $subscribed = $instagram->getFollowing($account->getId(),500);  // решил, что 500 подписок достаточно
        }catch (InstagramException $e){
            return "Не смог получить подписки";
        }

        $usernames   = array_map(function ($v){ return $v['username'];},$subscribed['accounts'] );

        $sources  = DB::select("SELECT * FROM `sources` WHERE `type`='instagram' AND `active` > 0;");

        $unsubscribed = [];
        foreach ($sources as $source){
            if(!in_array($source->code,$usernames)){
                $unsubscribed[]   = $source->code;
            }
        }

        $config->dropErrors();
        return "Не подписано ".count($unsubscribed)." из ".count($sources)." персон(обрабатываем <= 500)";
    }
}
