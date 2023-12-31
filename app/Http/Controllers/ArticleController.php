<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Pagination\LengthAwarePaginator;
use PhpOffice\PhpSpreadsheet\{IOFactory,Spreadsheet};
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{

    public function index(Request $request){
        $search   = $this->getSearch($request);
        $articles = $this->getArticles($search,500);

        return view('articles.index',[
            'search'    => $search,
            'articles'  => $articles,
        ]);
    }

    public function apiIndex(Request $request){
        $search     = $this->getSearch($request);
        $paginator  = $this->getArticles($search,500);

        $items      = $paginator->items();
        $articles   = [];
        foreach ($items as $item) {
            $article                        = new \stdClass();
            $article->id                    = $item->id;
            $article->title                 = $item->title;
            $article->foreign_title         = $item->foreign_title;
            $article->link                  = $item->link;
            $article->pub_date              = $item->pub_date;
            $article->categories            = $item->categories;
            $article->unknown_categories    = $item->unknown_categories;
            $article->haveText              = intval(strlen($item->original_text) > 0);
            $article->source                = [     'id'        => $item->source->id,
                                                    'name'      => $item->source->name,
                                                    'link'      => $item->source->link,
                                                    'stream'    => $item->source->stream,
                                                    'foreign'   => $item->source->foreign ];

            $articles[]  = $article;
        }

        return response()->json([
            'lastPage'      => $paginator->lastPage(),
            'articles'      => $articles,
            'streams'       => [ '0' =>'Все потоки'] + \App\RssSource::$streams,
            'categories'    => [ 0 => ' Все категории '] + \App\Article::$categories
        ]);
    }

    public function buckingham(Request $request){
        $search   = $this->getSearch($request,strtotime('-2 day'));

        $search->foreign    = 2;
        $search->stream     = \App\RssSource::STREAM_BUCKINGHAM;
        $search->searchQuery    = 'Букингем | Букингемский | Елизавета | Меган | Миддлтон | Уильям | Маркл | принц |'.
            ' принцесса | королева | Чарльз | Камилла | Виндзор | Виндзорский | Виндзоры | Королевский | Королевская |'.
            ' Королевские | Королевскую | Королевского | Королевских | Король | Королева | Филипп | монарх | монархический | Диана | Гарри';

        $articles = $this->getArticles($search,500);

        return view('articles.buckingham',[
            'search'    => $search,
            'articles'  => $articles,
        ]);
    }

    public function text(Request $request){
        return view('articles.text',[
            'article'   => \App\Article::findOrFail((int) $request->id),
        ]);
    }

    public function apiText(Request $request){
        $article    = \App\Article::findOrFail((int) $request->id);

        unset($article['source_id'],$article['categories'],$article['unknown_categories']);
        unset($article['external_id'],$article['translate']);

        return response()->json($article);
    }


    public function translate(Request $request){
        $article    = \App\Article::findOrFail((int) $request->id);

        if((strlen($article->translated_text)>0)&&(strlen($article->title)>0)){
            return [
                'success'           => true,
                'translatedTitle'   => $article->title,
                'translatedText'    => str_replace("\n","<br>\n",$article->translated_text),
            ];
        }

        if(!strlen($article->title) && strlen(trim($article->foreign_title))){
            $article->title     = \YandexTranslate::single($article->foreign_title);
            $article->translate = 0;
            $article->save();
        }

        if(!strlen($article->translated_text) && strlen(trim($article->original_text))){
            $article->translated_text = \YandexTranslate::large($article->original_text);
            $article->save();
        }

        return [
            'success'           => true,
            'translatedTitle'   => $article->title,
            'translatedText'    => str_replace("\n","<br>\n",$article->translated_text),
        ];
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
        if(($search->stream > 0)||($search->foreign > 0)){
            $sourceDb = \App\RssSource::query();

            if($search->stream > 0){
                $sourceDb = $sourceDb->where( \DB::raw("(`stream` & ".intval($search->stream).")") ,'>',0);
            }

            if($search->foreign === 1){
                $sourceDb = $sourceDb->where('foreign','=',0);
            }elseif($search->foreign === 2){
                $sourceDb = $sourceDb->where('foreign','=',1);
            }

            $ids = $sourceDb->pluck('id');
            $sourceCond = 'AND source_id IN ('.implode(',',$ids->toArray()).') ';
        }elseif($search->source > 0){
            $sourceCond = "AND source_id = {$search->source} ";
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

        if(($search->stream > 0)||($search->foreign > 0)){
            $sourceDb = \App\RssSource::query();

            if($search->stream > 0){
                $sourceDb = $sourceDb->where( \DB::raw("(`stream` & ".intval($search->stream).")") ,'>',0);
            }

            if($search->foreign === 1){
                $sourceDb = $sourceDb->where('foreign','=',0);
            }elseif($search->foreign === 2){
                $sourceDb = $sourceDb->where('foreign','=',1);
            }

            $ids = $sourceDb->pluck('id');
            $db  = $db->whereIn('source_id',$ids->toArray());
        }elseif($search->source > 0){
            $db  = $db->where('source_id',$search->source );
        }

        if($search->category){
            $db  = $db->where( \DB::raw("(`categories` & ".intval($search->category).")") ,'>', 0);
        }

        $db = $db->orderBy('pub_date', 'desc');

        if($paginate){
            $articles = $db->paginate($paginate);
        }else{
            $articles = $db->get()->all();
        }

        return $articles;
    }

    private function getSearch($request, $from = null){
        $search             = new \stdClass();
        $search->stream     = (int) ($request->stream       ?? 0);
        $search->source     = (int) ($request->source       ?? 0);
        $search->category   = (int) ($request->category     ?? 0);
        $search->from       = $request->from                ?? date('Y-m-d 00:00:00', $from ? $from : time());
        $search->to         = $request->to                  ?? date('Y-m-d 23:59:59');
        $search->translate  = (int) ($request->translate    ?? 1); // влияет только на отображение
        $search->foreign    = (int) ($request->foreign      ?? 0);

        $search->searchQuery  = (isset($request->searchQuery)) ? trim($request->searchQuery) : '';

        return $search;
    }
}
