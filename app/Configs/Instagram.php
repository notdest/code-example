<?php

namespace App\Configs;


use App\Mail\InstagramSessionExpired;
use Illuminate\Support\Facades\Mail;
use Phpfastcache\Helper\Psr16Adapter;

class Instagram extends Config
{
    public $login               = '';
    public $password            = '';
    public $session             = '';
    public $enabled             = 0;
    public $proxy               = '';
    public $emails              = '';
    public $enableSubscription  = 0;
    public $feedMaxPages        = 1;
    public $notes               = '';
    public $enableStories       = 0;

    // Технические параметры, они не видны на странице настроек
    public $lastSubscribe   = 0;    // timeStamp когда в последний раз пытались подписываться на персон
    public $errors          = 0;    // количество ошибок подряд, когда парсим. При превышении отключаем парсер

    private static $client = null;

    protected function getName():string{
        return "instagram";
    }

    protected function fieldNames():array{
        return [
            'login'                 => 'Логин (legacy)',
            'password'              => 'Пароль',
            'session'               => 'Сессия (legacy)',
            'enabled'               => 'Скачивание постов',
            'proxy'                 => 'Прокси',
            'emails'                => 'Почта для оповещений',
            'enableSubscription'    => 'Включено подписывание',
            'lastSubscribe'         => 'Время последнего подписывания',
            'errors'                => 'Ошибок авторизации подряд',
            'feedMaxPages'          => 'Ограничение постов в минуту',
            'enableStories'         => 'Скачивать сторис',
            'notes'                 => 'Заметки',
        ];
    }

    public function getClient(){
        if(self::$client === null){
            $httpConfig = $this->proxy ? ['proxy' => $this->proxy] : [];

            try {
                $instagram = \InstagramScraper\Instagram::withCredentials(
                    new \GuzzleHttp\Client($httpConfig),
                    $this->login,
                    $this->password,
                    new Psr16Adapter('Files')
                );

                if ($this->session) {
                    $instagram->loginWithSessionId($this->session);
                } else {
                    $instagram->login(); // по умолчанию ищет закешированную saveSession()
                    $instagram->saveSession(86400);
                }
            }catch (\Exception $e){     // не обязательно будет InstagramAuthException
                if($this->tooMuchErrors()){
                    Mail::to( $this->getEmails() )->send(new InstagramSessionExpired());
                }
                throw $e;
            }

            self::$client   = $instagram;
        }

        return self::$client;
    }

    public function getEmails(){
        $addresses  = explode(',',$this->emails);

        $ret    = [];
        foreach ($addresses as $address){
            $parts  = explode('|',$address);
            $ret[]  = [ 'name' => trim($parts[0]), 'email' => trim($parts[1])];
        }
        return $ret;
    }

    public function dropErrors(){
        if($this->errors !== 0){
            $this->errors = 0;
            $this->save();
        }
    }

    private function tooMuchErrors():bool{
        $ret = false;
        $this->errors += 1;
        if ($this->errors >= 5){
            $this->enabled  = 0;
            $ret            = true;
        }
        $this->save();

        return $ret;
    }
}
