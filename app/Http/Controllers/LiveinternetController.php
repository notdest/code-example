<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class LiveinternetController extends Controller
{
    public function index(Request $request){
        [$tab,$date] = $this->getParams($request);

        $yesterday  = $this->getDay($date,$tab);

        if(!count($yesterday)){     //  Ночью ещё не спарсили
            $date       = strtotime('-1 day',$date);
            $yesterday  = $this->getDay($date,$tab);
        }

        $before     = $this->getDay(strtotime('-1 day',$date),$tab,true);


        $lastWeek       = $this->getPeriod($tab, strtotime('-6 day',$date), $date);
        $sites          = $lastWeek->pluck('site');
        $previousWeek   = $this->getPeriod($tab, strtotime('-13 day',$date), strtotime('-7 day',$date), $sites);

        $lastMonth      = $this->getPeriod($tab, strtotime('-29 day',$date), $date);
        $sites          = $lastMonth->pluck('site');
        $previousMonth  = $this->getPeriod($tab, strtotime('-59 day',$date), strtotime('-30 day',$date), $sites);


        $lastCalendarMonth      = $this->getPeriod($tab,    strtotime('first day of previous month'),
                                                            strtotime('last day of previous month'));
        $sites                  = $lastCalendarMonth->pluck('site');
        $previousCalendarMonth  = $this->getPeriod($tab,    strtotime('first day of -2 month'),
                                                            strtotime('last day of -2 month'), $sites);


        return view('liveinternet.index',[
            'tab'           => $tab,
            'date'          => $date,

            'yesterday'     => $yesterday,
            'before'        => $before,

            'lastWeek'      => $lastWeek,
            'previousWeek'  => $previousWeek,

            'lastMonth'     => $lastMonth,
            'previousMonth' => $previousMonth,

            'lastCalendarMonth'     => $lastCalendarMonth,
            'previousCalendarMonth' => $previousCalendarMonth,
        ]);
    }


    public function download(Request $request){
        [$tab,$date]    = $this->getParams($request);
        $table          = $request->table     ?? '';

        $names      = [
            'zen'               => "Переходы из Дзен",
            'yandex'            => "Переходы из Яндекс",
            'social'            => "Переходы из соцсетей",
            'ru'                => "Общие данные",
            's_googl'           => "Переходы из поиска Гугла",
            'n_y'               => "Яндекс новости",
            'n_g'               => "Google News",
            'android_google'    => "Гугл Дискавер",
        ];
        $tabName    = $names[$tab];

        $formatter      = \Jenssegers\Date\Date::createFromTimestamp($date);

        switch ($table) {
            case 'day':
                $filename   = $tabName." - динамика дня ".$formatter->format('j F');
                $left       = $formatter->format('j F');
                $right      = $formatter->subDay()->format('j F');

                $lastPeriod     = $this->getDay($date,$tab);
                $beforePeriod   = $this->getDay(strtotime('-1 day',$date),$tab,true);
                break;
            case 'week':
                $filename   = $tabName." - сумма за неделю ".$formatter->subDay(6)->format('j F'). " - " .$formatter->addDay(6)->format('j F');
                $left       = $formatter->subDay(6)->format('j F'). " - " .$formatter->addDay(6)->format('j F');
                $right      = $formatter->subDay(13)->format('j F')." - " .$formatter->addDay(6)->format('j F');

                $lastPeriod     = $this->getPeriod($tab, strtotime('-6 day',$date), $date);
                $sites          = $lastPeriod->pluck('site');
                $beforePeriod   = $this->getPeriod($tab, strtotime('-13 day',$date), strtotime('-7 day',$date), $sites);
                break;
            case 'month':
                $filename   = $tabName." - сумма за месяц ".$formatter->subDay(29)->format('j F'). " - " .$formatter->addDay(29)->format('j F');
                $left       = $formatter->subDay(29)->format('j F'). " - " .$formatter->addDay(29)->format('j F');
                $right      = $formatter->subDay(59)->format('j F')." - " .$formatter->addDay(29)->format('j F');

                $lastPeriod     = $this->getPeriod($tab, strtotime('-29 day',$date), $date);
                $sites          = $lastPeriod->pluck('site');
                $beforePeriod   = $this->getPeriod($tab, strtotime('-59 day',$date), strtotime('-30 day',$date), $sites);
                break;
            case 'calendar_month':
                $formatter  = \Jenssegers\Date\Date::now();
                $filename   = $tabName." - сумма за календарный месяц ".$formatter->subMonth()->day(1)->format('j F'). " - " .$formatter->format('t F');
                $left       = $formatter->format('j F'). " - " .$formatter->format('t F');
                $right      = $formatter->subMonth()->format('j F')." - " .$formatter->format('t F');

                $lastPeriod     = $this->getPeriod($tab,    strtotime('first day of previous month'),
                                                            strtotime('last day of previous month'));
                $sites          = $lastPeriod->pluck('site');
                $beforePeriod   = $this->getPeriod($tab,    strtotime('first day of -2 month'),
                                                            strtotime('last day of -2 month'), $sites);
                break;
            default:
                throw new \Exception('Unknown table');
        }

        $spreadsheet    = new Spreadsheet();
        $spreadsheet->getProperties()   ->setCreator('Parser')
                                        ->setLastModifiedBy('Parser')
                                        ->setTitle($filename)
                                        ->setSubject($filename);

        $sheet  = $spreadsheet->setActiveSheetIndex(0);
        $sheet  ->setCellValue('A1', 'Сайт')
                ->setCellValue('B1', $left)
                ->setCellValue('C1', $right)
                ->setCellValue('D1', 'Динамика (%)');

        $sheet  ->getColumnDimension('A')->setWidth(30);
        $sheet  ->getColumnDimension('B')->setWidth(25);
        $sheet  ->getColumnDimension('C')->setWidth(25);
        $sheet  ->getColumnDimension('D')->setWidth(16);

        $i  = 2;
        if($table === 'day') {
            foreach ($lastPeriod as $site => $views) {
                if (isset($beforePeriod[$site])) {
                    $sheet->setCellValue("A$i", $site)
                          ->setCellValue("B$i", number_format($views, 0, '.', ' '))
                          ->setCellValue("C$i", number_format($beforePeriod[$site], 0, '.', ' '))
                          ->setCellValue("D$i", number_format((($views - $beforePeriod[$site]) / $beforePeriod[$site]) * 100, 2, '.', ' '));
                    $i++;
                }
            }
        }elseif($table === 'calendar_month'){
            $period         = $lastPeriod->max('count');
            $previousPeriod = $beforePeriod->max('count');
            $beforePeriod   = $beforePeriod->keyBy('site');
            foreach ($lastPeriod as $row) {
                if(isset($beforePeriod[$row->site])){
                    $sheet  ->setCellValue("A$i", $row->site)
                            ->setCellValue("B$i", number_format($row->views,0,'.',' '))
                            ->setCellValue("C$i", number_format($beforePeriod[$row->site]->views,0,'.',' '));
                    if( ($row->count === $period) && ($beforePeriod[$row->site]->count === $previousPeriod) ){
                        $sheet->setCellValue("D$i", number_format(( ($row->views - $beforePeriod[$row->site]->views)/$beforePeriod[$row->site]->views)*100,2,'.',' '));
                    }else{
                        $sheet->setCellValue("D$i", $row->count ."дн. - ".$beforePeriod[$row->site]->count."дн.");
                    }
                    $i++;
                }
            }
            $sheet->setCellValue("A$i", "Собрано дней за ".$formatter->addMonth()->format('F').": $period;за ".
                                                        $formatter->subMonth()->format('F').": $previousPeriod ");
        }else{
            $period         = $lastPeriod->max('count');
            $beforePeriod   = $beforePeriod->keyBy('site');
            foreach ($lastPeriod as $row) {
                if(isset($beforePeriod[$row->site])){
                    $sheet  ->setCellValue("A$i", $row->site)
                            ->setCellValue("B$i", number_format($row->views,0,'.',' '))
                            ->setCellValue("C$i", number_format($beforePeriod[$row->site]->views,0,'.',' '));
                    if( ($row->count === $period) && ($beforePeriod[$row->site]->count === $period) ){
                        $sheet->setCellValue("D$i", number_format(( ($row->views - $beforePeriod[$row->site]->views)/$beforePeriod[$row->site]->views)*100,2,'.',' '));
                    }else{
                        $sheet->setCellValue("D$i", $row->count ."дн. - ".$beforePeriod[$row->site]->count."дн.");
                    }
                    $i++;
                }
            }
            $sheet->setCellValue("A$i", "Собрано дней: $period");
        }

        $headers = [
            "Content-type"              => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "Cache-Control"             => "max-age=0",
        ];

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(
            function() use ($writer){ $writer->save('php://output');},
            $filename.".xlsx",
            $headers
        );
    }

    private function getParams($request){
        $tab    = $request->tab     ?? 'zen';
        $day    = $request->date    ?? '-1 day';
        if(!in_array($tab,['zen','social','yandex','ru', 's_googl', 'n_y', 'n_g', 'android_google'])){
            $tab    = 'zen';
        }
        $date       = strtotime($day);

        return [$tab,$date];
    }


    private function getDay($date,$tab,$secondary = false){
        $db = DB::table('liveinternet') ->where('day',date("Y-m-d",$date))
                                        ->where('type',$tab);

        if(!$secondary){
            $db = $db->orderBy('views','desc')->limit(50);
        }

        return $db->pluck('views','site');
    }

    private function getPeriod($tab,$from,$to,$sites = null){
        $secondary  = !is_null($sites);
        $db = DB::table('liveinternet') ->select(DB::raw('`site`, SUM(`views`) AS `views`, COUNT(`views`) AS `count`'))
                                        ->where('type',$tab)
                                        ->where('day','>=',date("Y-m-d",$from ))
                                        ->where('day','<=',date("Y-m-d",$to ))
                                        ->groupBy('site');

        if($secondary){
            $db = $db->whereIn('site',$sites);
        }else{
            $db = $db->orderBy('views','desc')->limit(50);
        }

        return $db->get();
    }
}
