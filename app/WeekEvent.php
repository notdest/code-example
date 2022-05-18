<?php

namespace App;


use Illuminate\Database\Eloquent\Model;

class WeekEvent extends Model
{
    protected $fillable     = ['title', 'category', 'start', 'end'];
}
