<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\{IOFactory,Spreadsheet};

class ArticleController extends Controller
{
    //
    public function index(Request $request){

        $search         = new \stdClass();
        $search->from   = $request->from    ?? date('Y-m-d 00:00:00');
        $search->to     = $request->to      ?? date('Y-m-d 23:59:59');

        $articles   = \App\Article::where('pub_date','>=',$search->from )
                                  ->where('pub_date','<=',$search->to )
                                  ->orderBy('pub_date', 'desc')
                                  ->paginate(15);

        return view('articles.index',[
            'search'    => $search,
            'articles'  => $articles,
        ]);
    }



    public function download(Request $request){
        $search         = new \stdClass();
        $search->from   = $request->from    ?? date('Y-m-d 00:00:00');
        $search->to     = $request->to      ?? date('Y-m-d 23:59:59');

        $articles   = \App\Article::where('pub_date','>=',$search->from )
                                  ->where('pub_date','<=',$search->to )
                                  ->orderBy('pub_date', 'desc')
                                  ->get()->all();


        $spreadsheet = new Spreadsheet();

        $spreadsheet->getProperties()->setCreator('Parser')
                    ->setLastModifiedBy('Parser')
                    ->setTitle(  "Выгрузка статей $search->from - $search->to")
                    ->setSubject("Выгрузка статей $search->from - $search->to");

        $sheet  = $spreadsheet->setActiveSheetIndex(0);

        $sheet  ->setCellValue('A1', 'Заголовок')
                ->setCellValue('B1', 'Автор')
                ->setCellValue('C1', 'Источник')
                ->setCellValue('D1', 'Дата публикации')
                ->setCellValue('E1', 'Ссылка')
                ->setCellValue('F1', 'Лид')
                ->setCellValue('G1', 'Категория');

        $sheet  ->getColumnDimension('A')->setWidth(70);
        $sheet  ->getColumnDimension('B')->setWidth(20);
        $sheet  ->getColumnDimension('C')->setWidth(20);
        $sheet  ->getColumnDimension('D')->setWidth(20);
        $sheet  ->getColumnDimension('E')->setWidth(25);
        $sheet  ->getColumnDimension('F')->setWidth(30);
        $sheet  ->getColumnDimension('G')->setWidth(20);

        foreach ($articles as $v => $article){
            $i  = $v+2;
            $sheet  ->setCellValue("A$i", $article->title)
                    ->setCellValue("B$i", $article->author)

                    ->setCellValue("C$i", $article->source)
                    ->getCell("C$i")->getHyperlink()->setUrl($article->source)
                    ->setTooltip('Перейти на сайт источника');

            $sheet  ->setCellValue("D$i", $article->pub_date)

                    ->setCellValue("E$i", $article->link)
                    ->getCell("E$i")->getHyperlink()->setUrl($article->link)
                    ->setTooltip('Перейти в статью');

            $sheet  ->setCellValue("F$i", strip_tags($article->description))
                    ->setCellValue("G$i", $article->category);
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
