<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\InstagramSessionExpired;
use InstagramScraper\Exception\InstagramException;


class InstagramController extends Controller
{
    public static $latch = '/var/www/public/img_cache/lost_posts_latch.txt';



    public function edit(){

        $config = new \App\Configs\Instagram();
        return view('instagram.edit',[
            'config'        => $config,
            'lostChecking'  => $this->latchLocked(),
        ]);
    }

    public function save(Request $request){
        $config = new \App\Configs\Instagram();

        $fields = $request->except('_token');
        $validator  = \Validator::make($fields,[
            'enabled'       => 'boolean',
            'enableStories' => 'boolean',
            'feedMaxPages'  => 'required|int',
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
            'lostChecking'  => $this->latchLocked(),
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

        $alreadyFollowings  = array_map(function ($v){ return $v['username'];},$subscribed['accounts'] );
        $requiredFollowings = DB::table('sources')->where('type','=','instagram')->where('active','>','0')
                                                                                 ->pluck('code')->all();

        $unsubscribed = [];
        foreach ($requiredFollowings as $code){
            if(!in_array($code,$alreadyFollowings)){
                $unsubscribed[]   = $code;
            }
        }

        $excess = [];
        foreach ($alreadyFollowings as $code) {
            if(!in_array($code,$requiredFollowings)){
                $excess[]   = $code;
            }
        }

        $config->dropErrors();
        return "Не подписано ".count($unsubscribed)." из ".count($requiredFollowings)." персон(обрабатываем <= 500). ".
            "Лишних ".count($excess).' персон.';
    }

    public function showLostPosts(){
        $file = '/var/www/public/img_cache/lost_posts.txt';
        if(!file_exists($file)){
            return "Не найден файл с результатом";
        }else{
            return file_get_contents($file);
        }
    }

    public function checkLostPosts(){
        if($this->latchLocked()){
            return "already launched";
        }else{
            dispatch( new \App\Jobs\InstagramLostPosts());
            return "ok";
        }
    }

    private function latchLocked(){
        return (file_exists(self::$latch) && (time()-filemtime(self::$latch)) < 800);
    }
}
