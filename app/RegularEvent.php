<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegularEvent extends Model
{

    /*
        !!!!!!!!!!! год приводим к 2020 в БД !!!!!!!!!!

        Это повторяющиеся события, нам от них нужен только день и месяц. Но я не хотел колхозить из полей в БД
        собственный тип данных с датой, поэтому используем стандартный DATETIME, у всех дат год приводим к 2020 в БД,
        и к текущему году в объектах. Все эти действия я пытаюсь спрятать в этом классе
     */
    public static function getComingMonths($months,$search){
        $endMonth = intval(date('n')) + $months;

        $db     = self::where('end','>=',date('2020-m-d 00:00:00',strtotime('-1 day')) );
        $db     = $db->where('start','<',($endMonth>12) ? "2021-01-01 00:00:00":"2020-{$endMonth}-01 00:00:00");

        if($search->category > 0){
            $db = $db->where('category', $search->category);
        }

        $db     = $db->orderBy('start', 'asc');
        $events = $db->get();

        if($endMonth > 13){                 // добираем месяцы в следующем году
            $endMonth   -= 12;
            $db2    = self::where('end','>=','2020-01-01 00:00:00');
            $db2    = $db2->where('start','<',"2020-{$endMonth}-01 00:00:00");

            if($search->category > 0){
                $db2 = $db2->where('category', $search->category);
            }

            $db2    = $db2->orderBy('start', 'asc');
            $events = $events->concat($db2->get());
        }

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

    protected function translateOutputDate($inputDate){
        $time   = strtotime($inputDate);
        $currentYear = (intval(date('n',$time))+1) >= intval(date('n'));

        return ($currentYear ?  date('Y'):  date('Y')+1).date('-m-d H:i:s',$time);
    }

    protected function translateInputDate($inputDate,$suffix){
        if(strlen($inputDate)>10){
            return date("2020-m-d H:i:s",strtotime($inputDate));
        }else{
            return date("2020-m-d ",strtotime($inputDate)).$suffix;
        }
    }

}
