<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'blocked', 'role', 'surname', 'show_posts', 'show_articles','department'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    static $fieldNames = [
        'id'            => 'Id',
        'name'          => 'Имя',
        'surname'       => 'Фамилия',
        'email'         => 'E-mail',
        'password'      => 'Пароль',
        'department'    => 'Отдел',
        'created_at'    => 'Дата регистрации',
        'blocked'       => 'Статус',
        'role'          => 'Роль',
        'show_posts'    => 'Доступ к постам',
        'show_articles' => 'Доступ к статьям',
    ];

    const ROLE_USER     = 1;
    const ROLE_EDITOR   = 2;
    const ROLE_ADMIN    = 3;

    static $roleNames = [
        self::ROLE_USER     => 'Пользователь',
        self::ROLE_EDITOR   => 'Редактор',
        self::ROLE_ADMIN    => 'Админ',
    ];

    public function isAdmin(){
        return ($this->role == self::ROLE_ADMIN) ;
    }

    public function isEditor(){
        return ($this->role == self::ROLE_EDITOR) ;
    }

    public function isBlocked(){
        return ($this->blocked > 0) ;
    }

    public function postsEnabled(){
        return ($this->show_posts > 0);
    }

    public function articlesEnabled(){
        return ($this->show_articles > 0);
    }
}
