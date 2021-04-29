<?php

namespace App\Configs;


class Instagram extends Config
{
    public $login       = '';
    public $password    = '';
    public $session     = '';
    public $enabled     = 0;
    public $proxy       = '';
    public $emails      = '';
    public $errors      = 0;

    protected function getName():string{
        return "instagram";
    }

    protected function fieldNames():array{
        return [
            'login'         => 'Логин',
            'password'      => 'Пароль',
            'session'       => 'Сессия',
            'enabled'       => 'Состояние',
            'proxy'         => 'Прокси',
            'emails'        => 'Почта для оповещений',
            'errors'        => 'Ошибок авторизации подряд',
        ];
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

    public function tooMuchErrors():bool{
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
