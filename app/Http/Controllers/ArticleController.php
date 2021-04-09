<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\{IOFactory,Spreadsheet};
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    //
    public function index(Request $request){
        $search   = $this->getSearch($request);
        $articles = $this->getArticles($search,15);

        return view('articles.index',[
            'search'    => $search,
            'articles'  => $articles,
        ]);
    }



    public function download(Request $request){
        $search   = $this->getSearch($request);
        $articles = $this->getArticles($search);


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


    private function getArticles($search,$paginate = 0){
        if (strlen($search->searchQuery) > 0) {
            return $this->getArticlesSphinx($search,$paginate);
        }else{
            return $this->getArticlesMysql($search,$paginate);
        }
    }

    private function getArticlesSphinx($search,$paginate = 0){
        $limit  = $paginate ?: 1000;
        $page   = Paginator::resolveCurrentPage();
        $offset = ($paginate) ? ($page - 1) * $limit : 0;

        $from   = strtotime($search->from);
        $to     = strtotime($search->to);

        $sourceCond = '';
        if($search->stream > 0){
            $ids = \App\RssSource::where( \DB::raw("(`stream` & ".intval($search->stream).")") ,'>',0)->pluck('id');
            $sourceCond = 'AND source_id IN ('.implode(',',$ids->toArray()).') ';
        }

        $categoryCond = '';
        if($search->category > 0){
            $categoryCond = "AND categories = {$search->category} ";
        }

        $where  =   "MATCH( ? ) ".
                    "AND pub_date >= $from ".
                    "AND pub_date <= $to ".
                    $sourceCond .
                    $categoryCond;

        $found  = DB::connection('sphinx')->select(
                        "SELECT * FROM rss WHERE $where LIMIT $offset,$limit ;",
                        [$search->searchQuery]);

        $ids    = array_map(function ($v){return $v->id;},$found);

        $articles = \App\Article::with('source')
                                ->whereIn('id',$ids )->get();

        if($paginate){
            $count  = DB::connection('sphinx')
                        ->select("SELECT COUNT(*) AS cnt FROM rss WHERE $where ;",[$search->searchQuery])[0]->cnt;
            return new LengthAwarePaginator($articles,$count,$limit,$page,['path' => Paginator::resolveCurrentPath()]);
        }else{
            return $articles;
        }
    }

    private function getArticlesMysql($search,$paginate = 0){

        $db = \App\Article::with('source')
                            ->where('pub_date','>=',$search->from )
                            ->where('pub_date','<=',$search->to );

        if($search->stream > 0){
            $ids = \App\RssSource::where( \DB::raw("(`stream` & ".intval($search->stream).")") ,'>',0)->pluck('id');
            $db  = $db->whereIn('source_id',$ids->toArray());
        }

        if($search->category){
            $db  = $db->where( \DB::raw("(`categories` & ".intval($search->category).")") ,'>', 0);
        }

        $db = $db->orderBy('pub_date', 'desc');

        if($paginate){
            $articles = $db->paginate(15);
        }else{
            $articles = $db->get()->all();
        }

        return $articles;
    }

    private function getSearch($request){
        $search             = new \stdClass();
        $search->stream     = (int) $request->stream        ?? 0;
        $search->category   = (int) $request->category      ?? 0;
        $search->from       = $request->from                ?? date('Y-m-d 00:00:00');
        $search->to         = $request->to                  ?? date('Y-m-d 23:59:59');

        $search->searchQuery  = (isset($request->searchQuery)) ? trim($request->searchQuery) : '';

        return $search;
    }
}
