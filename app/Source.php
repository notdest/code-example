<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Source extends Model
{
    protected $table    = 'sources';
    public $timestamps  = false;

    public static $fieldNames = [
        'id'        => 'Id',
        'code'      => 'Аккаунт',
        'name'      => 'Персона',
        'type'      => 'Соцсеть',
        'active'    => 'Статус',
    ];

    public static $types = [
        'instagram' => 'Instagram',
        'facebook'  => 'Facebook',
    ];

    protected $fillable = [
        'code', 'type','active'
    ];

    public function person(){
        return $this->belongsTo('App\Person','personId')->withDefault([
            'name'  => '',
        ]);
    }
}
