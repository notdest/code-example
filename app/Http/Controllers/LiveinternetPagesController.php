<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class LiveinternetPagesController extends Controller
{
    const SITE_RIA_TOTAL            = 1;
    const SITE_LENTA                = 2;
    const SITE_RBCRU                = 3;
    const SITE_RIA_SPORT            = 4;
    const SITE_TASS                 = 5;
    const SITE_AIF                  = 6;
    const SITE_RG_RU                = 7;
    const SITE_KOMMERSANT           = 8;
    const SITE_ECHO_MSK             = 9;
    const SITE_5_TV                 = 10;
    const SITE_INOSMI               = 11;
    const SITE_KOMMERSANT_TITLES    = 12;
    const SITE_REN_TV               = 13;
    const SITE_VEDOMOSTI            = 14;
    const SITE_TVRAIN               = 15;
    const SITE_3DNEWS               = 16;
    const SITE_DOCTORPITER_RU       = 17;
    const SITE_AIF_HEALTH           = 18;
    const SITE_AIF_SOCIETY          = 19;
    const SITE_RIA_PRIME            = 20;
    const SITE_FOREXPF_RU           = 21;
    const SITE_ANEKDOT_RU           = 22;

    public static $sites    = [
        self::SITE_KOMMERSANT_TITLES => 'kommersant.ru',
        self::SITE_REN_TV            => 'ren.tv',
        self::SITE_VEDOMOSTI         => 'vedomosti.ru',
        self::SITE_TVRAIN            => 'tvrain.ru',
        self::SITE_3DNEWS            => '3dnews.ru',
        self::SITE_DOCTORPITER_RU    => 'doctorpiter.ru',
        self::SITE_AIF_HEALTH        => 'aif.ru/health',
        self::SITE_AIF_SOCIETY       => 'aif.ru/society',
        self::SITE_RIA_PRIME         => '1prime.ru',
        self::SITE_FOREXPF_RU        => 'forexpf.ru',
        self::SITE_ANEKDOT_RU        => 'anekdot.ru',
    ];




    public function index(Request $request){
        $day    = $request->date    ?? '-1 day';
        $date   = strtotime($day);

        $yesterday  = $this->getDay($date);
        if(!count($yesterday)){     //  Ночью ещё не спарсили
            $date       = strtotime('-1 day',$date);
            $yesterday  = $this->getDay($date);
        }

        $pages      = $yesterday->pluck('page_hash');
        $before     = [];
        $tmpDate    = $date;
        for($i=0;$i<6;$i++){
            $tmpDate    = strtotime('-1 day',$tmpDate);
            $dayResult  = $this->getDay($tmpDate,$pages)->pluck('views','page_hash')->all();
            $before[$i] = $dayResult;
        }

        $lastWeek       = $this->getPeriod(strtotime('-6 day',$date), $date);
        $sites          = $lastWeek->pluck('page_hash');
        $previousWeek   = $this->getPeriod(strtotime('-13 day',$date), strtotime('-7 day',$date), $sites);

        $lastMonth      = $this->getPeriod( strtotime('first day of previous month'),
                                            strtotime('last day of previous month'));
        $sites          = $lastMonth->pluck('page_hash');
        $previousMonth  = $this->getPeriod( strtotime('first day of -2 month'),
                                            strtotime('last day of -2 month'), $sites);

        return view('liveinternetPages.index',[
            'date'          => $date,
            'yesterday'     => $yesterday,
            'before'        => $before,

            'lastWeek'      => $lastWeek,
            'previousWeek'  => $previousWeek,

            'lastMonth'     => $lastMonth,
            'previousMonth' => $previousMonth,
        ]);
    }




    public function download(Request $request){
        $day    = $request->date    ?? '-1 day';
        $table  = $request->table   ?? '';
        $date   = strtotime($day);

        $formatter      = \Jenssegers\Date\Date::createFromTimestamp($date);

        switch ($table) {
            case 'day':
                $filename   = "Статьи LiveInternet - динамика по дням ".$formatter->format('j F');

                $yesterday = $this->getDay($date);
                $pages      = $yesterday->pluck('page_hash');
                $before     = [];
                $tmpDate    = $date;
                for($i=0;$i<6;$i++){
                    $tmpDate    = strtotime('-1 day',$tmpDate);
                    $dayResult  = $this->getDay($tmpDate,$pages)->pluck('views','page_hash')->all();
                    $before[$i] = $dayResult;
                }
                break;
            case 'day_all':
                $filename   = "Статьи LiveInternet - динамика по дням полная ".$formatter->format('j F');

                $yesterday = $this->getDay($date,[],true);
                $pages      = $yesterday->pluck('page_hash');
                $before     = [];
                $tmpDate    = $date;
                for($i=0;$i<6;$i++){
                    $tmpDate    = strtotime('-1 day',$tmpDate);
                    $dayResult  = $this->getDay($tmpDate,$pages)->pluck('views','page_hash')->all();
                    $before[$i] = $dayResult;
                }
                break;

            case 'week':
                $filename   = "Статьи LiveInternet - динамика по неделям ".$formatter->subDay(6)->format('j F'). " - " .$formatter->addDay(6)->format('j F');
                $left       = $formatter->subDay(6)->format('j F'). " - " .$formatter->addDay(6)->format('j F');
                $right      = $formatter->subDay(13)->format('j F')." - " .$formatter->addDay(6)->format('j F');

                $lastPeriod     = $this->getPeriod(strtotime('-6 day',$date), $date);
                $sites          = $lastPeriod->pluck('page_hash');
                $beforePeriod   = $this->getPeriod(strtotime('-13 day',$date), strtotime('-7 day',$date), $sites);

                $periodLeft = $periodRight = $lastPeriod->max('count');
                $bottomInscription = "Собрано дней: $periodLeft";
                break;

            case 'month':
                $formatter  = \Jenssegers\Date\Date::now();
                $filename   = "Статьи LiveInternet - динамика по месяцам ".$formatter->subMonth()->day(1)->format('j F'). " - " .$formatter->format('t F');
                $left       = $formatter->format('j F'). " - " .$formatter->format('t F');
                $right      = $formatter->subMonth()->format('j F')." - " .$formatter->format('t F');

                $lastPeriod     = $this->getPeriod( strtotime('first day of previous month'),
                                                    strtotime('last day of previous month'));
                $sites          = $lastPeriod->pluck('page_hash');
                $beforePeriod   = $this->getPeriod( strtotime('first day of -2 month'),
                                                    strtotime('last day of -2 month'), $sites);

                $periodLeft     = $lastPeriod->max('count');
                $periodRight    = $beforePeriod->max('count');
                $bottomInscription = "Собрано дней за ".$formatter->addMonth()->format('F').": $periodLeft;за ".
                                                        $formatter->subMonth()->format('F').": $periodRight ";
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

        $format     = function($number){
            return number_format( $number,0,'.',' ');
        };
        $i  = 2;
        if(in_array($table,['day','day_all'])){
            $sheet  ->setCellValue('A1', 'Сайт')
                    ->setCellValue('B1', 'Статья')
                    ->setCellValue('C1', $formatter->format('j F'))
                    ->setCellValue('D1', $formatter->subDay()->format('j F'))
                    ->setCellValue('E1', $formatter->subDay()->format('j F'))
                    ->setCellValue('F1', $formatter->subDay()->format('j F'))
                    ->setCellValue('G1', $formatter->subDay()->format('j F'))
                    ->setCellValue('H1', $formatter->subDay()->format('j F'))
                    ->setCellValue('I1', $formatter->subDay()->format('j F'));

            $sheet  ->getColumnDimension('A')->setWidth(20);
            $sheet  ->getColumnDimension('B')->setWidth(90);


            foreach($yesterday as $row){
                $sheet->setCellValue("A$i", self::$sites[$row->site])
                      ->setCellValue("B$i", html_entity_decode($row->page))
                      ->setCellValue("C$i", $format($row->views))
                      ->setCellValue("D$i", isset($before[0][$row->page_hash]) ? $format($before[0][$row->page_hash]) : '')
                      ->setCellValue("E$i", isset($before[1][$row->page_hash]) ? $format($before[1][$row->page_hash]) : '')
                      ->setCellValue("F$i", isset($before[2][$row->page_hash]) ? $format($before[2][$row->page_hash]) : '')
                      ->setCellValue("G$i", isset($before[3][$row->page_hash]) ? $format($before[3][$row->page_hash]) : '')
                      ->setCellValue("H$i", isset($before[4][$row->page_hash]) ? $format($before[4][$row->page_hash]) : '')
                      ->setCellValue("I$i", isset($before[5][$row->page_hash]) ? $format($before[5][$row->page_hash]) : '');

                $i++;
            }
        }else{
            $sheet  ->setCellValue('A1', 'Сайт')
                    ->setCellValue('B1', 'Статья')
                    ->setCellValue('C1', $left)
                    ->setCellValue('D1', $right)
                    ->setCellValue('E1', 'Динамика (%)');

            $sheet  ->getColumnDimension('A')->setWidth(20);
            $sheet  ->getColumnDimension('B')->setWidth(90);
            $sheet  ->getColumnDimension('C')->setWidth(20);
            $sheet  ->getColumnDimension('D')->setWidth(20);
            $sheet  ->getColumnDimension('E')->setWidth(15);

            $beforePeriod   = $beforePeriod->keyBy(function ($item) {
                return $item->page_hash.'_'.$item->site;
            });

            foreach($lastPeriod as $row){
                $prevRow = isset($beforePeriod[$row->page_hash.'_'.$row->site]) ? $beforePeriod[$row->page_hash.'_'.$row->site]: null;

                $sheet->setCellValue("A$i", self::$sites[$row->site])
                      ->setCellValue("B$i", html_entity_decode($row->page))
                      ->setCellValue("C$i", $format($row->views))
                      ->setCellValue("D$i", $prevRow ? $format( $prevRow->views) : '');

                if($prevRow){
                    if(($row->count === $periodLeft) && ($prevRow->count === $periodRight)){
                        $sheet->setCellValue("E$i", number_format( ($row->views - $prevRow->views)/$prevRow->views*100,2,'.',' '));
                    }else{
                        $sheet->setCellValue("E$i", $row->count ."дн. - ".$prevRow->count."дн.");
                    }
                }

                $i++;
            }
            $sheet->setCellValue("A$i", $bottomInscription);
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

    private function getDay($date,$pages = [],$unlimited = false){
        $db = DB::table('liveinternet_pages') ->where('day',date("Y-m-d",$date));

        if(count($pages)){
            $db = $db->whereIn('page_hash',$pages);
        }else{
            $db = $db->orderBy('views','desc');
            if(!$unlimited){
                $db = $db->limit(100);
            }
        }

        return $db->get();
    }

    private function getPeriod($from,$to,$pages = []){
        $db = DB::table('liveinternet_pages');
        if(count($pages)){
            $db     = $db->select(DB::raw('`page_hash`,`site`, SUM(`views`) AS `views`, COUNT(`views`) AS `count`'));
        }else{
            $db     = $db->select(DB::raw('`page_hash`,`site`, MIN(`page`) AS `page`, SUM(`views`) AS `views`, COUNT(`views`) AS `count`'));
        }

        $db     = $db   ->where('day','>=',date("Y-m-d",$from ))
                        ->where('day','<=',date("Y-m-d",$to ))
                        ->groupBy('page_hash','site');

        if(count($pages)){
            $db = $db->whereIn('page_hash',$pages);
        }else{
            $db = $db->orderBy('views','desc')->limit(100);
        }

        return $db->get();
    }
}
