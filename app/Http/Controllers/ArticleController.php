<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\{IOFactory,Spreadsheet};

class ArticleController extends Controller
{
    //
    public function index(Request $request){

        $search         = new \stdClass();
        $search->stream = (int) $request->stream  ?? 0;
        $search->from   = $request->from    ?? date('Y-m-d 00:00:00');
        $search->to     = $request->to      ?? date('Y-m-d 23:59:59');

        $db         = \App\Article::with('source')
                                  ->where('pub_date','>=',$search->from )
                                  ->where('pub_date','<=',$search->to );

        if($search->stream > 0){
            $ids = \App\RssSource::where( \DB::raw("(`stream` & ".intval($search->stream).")") ,'>',0)->pluck('id');;
            $db  = $db->whereIn('source_id',$ids->toArray());
        }

        $articles   = $db->orderBy('pub_date', 'desc')
                         ->paginate(15);

        return view('articles.index',[
            'search'    => $search,
            'articles'  => $articles,
        ]);
    }



    public function download(Request $request){
        $search         = new \stdClass();
        $search->stream = (int) $request->stream  ?? 0;
        $search->from   = $request->from    ?? date('Y-m-d 00:00:00');
        $search->to     = $request->to      ?? date('Y-m-d 23:59:59');

        $db         = \App\Article::with('source')
                                  ->where('pub_date','>=',$search->from )
                                  ->where('pub_date','<=',$search->to );

        if($search->stream > 0){
            $ids = \App\RssSource::where( \DB::raw("(`stream` & ".intval($search->stream).")") ,'>',0)->pluck('id');;
            $db  = $db->whereIn('source_id',$ids->toArray());
        }

        $articles   = $db->orderBy('pub_date', 'desc')
                         ->get()->all();


        $spreadsheet = new Spreadsheet();

        $spreadsheet->getProperties()->setCreator('Parser')
                    ->setLastModifiedBy('Parser')
                    ->setTitle(  "Выгрузка статей $search->from - $search->to")
                    ->setSubject("Выгрузка статей $search->from - $search->to");

        $sheet  = $spreadsheet->setActiveSheetIndex(0);

        $sheet  ->setCellValue('A1', 'Заголовок')
                ->setCellValue('B1', 'Источник')
                ->setCellValue('C1', 'Дата публикации')
                ->setCellValue('D1', 'Ссылка')
                ->setCellValue('E1', 'Категория');

        $sheet  ->getColumnDimension('A')->setWidth(70);
        $sheet  ->getColumnDimension('B')->setWidth(20);
        $sheet  ->getColumnDimension('C')->setWidth(20);
        $sheet  ->getColumnDimension('D')->setWidth(25);
        $sheet  ->getColumnDimension('E')->setWidth(20);

        foreach ($articles as $v => $article){
            $i  = $v+2;
            $sheet  ->setCellValue("A$i", $article->title)
                    ->setCellValue("B$i", $article->source->name);

            $sheet  ->setCellValue("C$i", $article->pub_date)

                    ->setCellValue("D$i", $article->link)
                    ->getCell("D$i")->getHyperlink()->setUrl($article->link)
                    ->setTooltip('Перейти в статью');

            $sheet  ->setCellValue("E$i", $article->category);
        }

        $headers = [
            "Content-type"              => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "Cache-Control"             => "max-age=0",
        ];

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');

        return response()->streamDownload(
            function() use ($writer){ $writer->save('php://output');},
            "rss_".date('Y-m-d').".xlsx",
            $headers
        );
    }
}
