<?php

namespace App\Configs;


class Instagram extends Config
{
    public $login       = '';
    public $password    = '';
    public $session     = '';
    public $enabled     = 0;
    public $proxy       = '';

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
        ];
    }
}
