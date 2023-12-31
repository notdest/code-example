<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegularEvent extends Model
{
    private $year   = 0;

    public $regular     = true;
    public $weekEvent   = 0;
    /*
        !!!!!!!!!!! год приводим к 2020 в БД !!!!!!!!!!

        Это повторяющиеся события, нам от них нужен только день и месяц. Но я не хотел колхозить из полей в БД
        собственный тип данных с датой, поэтому используем стандартный DATETIME, у всех дат год приводим к 2020 в БД,
        и к текущему году в объектах. Все эти действия я пытаюсь спрятать в этом классе
     */
    public static function getMonth($search){
        $endMonth = $search->month + 1;

        $db     = self::where('end', '>=',"2020-{$search->month}-01 00:00:00" );
        $db     = $db->where('start','<',($endMonth>12) ? "2021-01-01 00:00:00":"2020-{$endMonth}-01 00:00:00");

        if($search->category > 0){
            $db = $db->where('category', $search->category);
        }

        $db     = $db->orderBy('start', 'asc');
        $events = $db->get();

        $events->each(function ($item,$key) use($search){
            $item->setYear($search->year);
        });
        return $events;
    }

    public function getStartAttribute($value){
        return $this->translateOutputDate($value);
    }

    public function getEndAttribute($value){
        return $this->translateOutputDate($value);
    }

    public function setStartAttribute($value){
        $this->attributes['start']  = $this->translateInputDate($value,'00:00:00');
    }

    public function setEndAttribute($value){
        $this->attributes['end']    = $this->translateInputDate($value,'23:59:59');
    }

    public function setYear(int $year){
        $this->year = $year;
    }

    protected function translateOutputDate($inputDate){
        $time   = strtotime($inputDate);

        return ($this->year > 0) ? $this->year.date('-m-d H:i:s',$time) : date('Y').date('-m-d H:i:s',$time);
    }

    protected function translateInputDate($inputDate,$suffix){
        if(strlen($inputDate)>10){
            return date("2020-m-d H:i:s",strtotime($inputDate));
        }else{
            return date("2020-m-d ",strtotime($inputDate)).$suffix;
        }
    }

}
