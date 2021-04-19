<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Trend;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class TrendController extends Controller
{
    public function index(Request $request){
        $search = $this->getSearch($request);
        $trends = $this->getTrends($search);

        return view('trends.index',[
            'trends'    => $trends,
            'search'    => $search,
        ]);
    }

    public function download(Request $request){
        $search = $this->getSearch($request);
        $trends = $this->getTrends($search);

        $spreadsheet = new Spreadsheet();

        $spreadsheet->getProperties()->setCreator('Parser')
                    ->setLastModifiedBy('Parser')
                    ->setTitle(  "Выгрузка Google Trends $search->from - $search->to")
                    ->setSubject("Выгрузка Google Trends $search->from - $search->to");

        $sheet  = $spreadsheet->setActiveSheetIndex(0);

        $sheet  ->setCellValue('A1', 'Тренд')
                ->setCellValue('B1', 'Дата')
                ->setCellValue('C1', 'Запросов')
                ->setCellValue('D1', 'Новость 1')
                ->setCellValue('E1', 'Источник 1')
                ->setCellValue('F1', 'Новость 2')
                ->setCellValue('G1', 'Источник 2');


        $sheet  ->getColumnDimension('A')->setWidth(45);
        $sheet  ->getColumnDimension('B')->setWidth(20);
        $sheet  ->getColumnDimension('C')->setWidth(10);
        $sheet  ->getColumnDimension('D')->setWidth(65);
        $sheet  ->getColumnDimension('E')->setWidth(25);
        $sheet  ->getColumnDimension('F')->setWidth(65);
        $sheet  ->getColumnDimension('G')->setWidth(25);

        foreach ($trends as $k => $trend){
            $i  = $k+2;
            $sheet  ->setCellValue("A$i", $trend->title)
                    ->setCellValue("B$i", $trend->pubDate)
                    ->setCellValue("C$i", $trend->traffic);

            if(isset($trend->news[0])){
                $news = $trend->news[0];
                $sheet->setCellValue("D$i", $news->news_item_title)
                      ->getCell("D$i")->getHyperlink()->setUrl($news->news_item_url)
                      ->setTooltip('Перейти в статью');

                $sheet->setCellValue("E$i", $news->news_item_source);
            }

            if(isset($trend->news[1])){
                $news = $trend->news[1];
                $sheet->setCellValue("F$i", $news->news_item_title)
                      ->getCell("F$i")->getHyperlink()->setUrl($news->news_item_url)
                      ->setTooltip('Перейти в статью');

                $sheet->setCellValue("G$i", $news->news_item_source);
            }
        }

        $headers = [
            "Content-type"  => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "Cache-Control" => "max-age=0",
        ];

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(
            function() use ($writer){ $writer->save('php://output');},
            "trends_".date('Y-m-d').".xlsx",
            $headers
        );
    }


    private function getSearch($request){
        $search     = new \stdClass();
        $search->feed       = (int)($request->feed      ?? Trend::FEED_RU);
        $search->from       =       $request->from      ?? date('Y-m-d 00:00:00',time()-7*3600*24);
        $search->to         =       $request->to        ?? date('Y-m-d 23:59:59');
        $search->sorting    = (int)($request->sorting   ?? Trend::SORTING_DATE);

        return $search;
    }

    private function getTrends($search){
        $db     = Trend::where('pubDate','>=',$search->from )
                       ->where('pubDate','<=',$search->to );

        if($search->feed > 0){
            $db     = $db->where('feed','=',$search->feed );
        }

        if($search->sorting === Trend::SORTING_TRAFFIC){
            $db     = $db->orderBy('traffic', 'desc');
        }else{
            $db     = $db->orderBy('pubDate', 'desc');
        }

        $trends     = $db->take(1000)->get();

        return $trends;
    }
}
