<?php

namespace App\Jobs;

use App\Http\Controllers\LiveinternetPagesController;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class parseLiveinternetPages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;
    protected $nextDate;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($input){
        $time           = strtotime($input);
        $this->date     = date('Y-m-d',$time);
        $this->nextDate = date('Y-m-d',strtotime('+1 day',$time));
    }


    public function middleware(){
        return [(new WithoutOverlapping())->dontRelease()->expireAfter(600)];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        [$sources,$sourcesTitles]   = $this->getSources();

        $count  =  DB::table('liveinternet_pages')->where('day', $this->date)->count();
        if($count > 0){
            throw new \Exception('already processed this date');
        }

        $httpClient = new \GuzzleHttp\Client();
        $jar        = \GuzzleHttp\Cookie\CookieJar::fromArray(['per_page' => '50'], 'www.liveinternet.ru');


        foreach ($sourcesTitles as $sourceId => $link) {
            $response   = $httpClient->request('GET', $link.'?date='.$this->date, ['cookies' => $jar]);
            $html       = $response->getBody()->getContents();
            sleep(1);

            if(!strpos($html,"date={$this->nextDate}\">")){         // ссылка "далее" будет вести на другую дату
                throw new \Exception('date less than minimum');     // если мы смотрим даты, которых уже нет
            }

            $rows       = $this->extractRows($html);

            $insertion  = [];
            foreach ($rows as $row) {
                $title  = $this->extractTitle($row);
                if(!$title) continue;

                $views  = $this->extractViews2($row);

                $insertion[]    = [ 'site' => $sourceId, 'page' => $title, 'page_hash' => md5($title), 'day' =>  $this->date, 'views' => $views];
            }
            DB::table('liveinternet_pages')->insert($insertion);
        }
    }

    private function getSources(){
        $pages  = [
            LiveinternetPagesController::SITE_RIA_TOTAL     => 'https://www.liveinternet.ru/stat/RS_Total/Riaru_Total/pages.html',
            LiveinternetPagesController::SITE_LENTA         => 'https://www.liveinternet.ru/stat/Lenta/pages.html',
            LiveinternetPagesController::SITE_RBCRU         => 'https://www.liveinternet.ru/stat/RBCRU/pages.html',
            LiveinternetPagesController::SITE_RIA_SPORT     => 'https://www.liveinternet.ru/stat/RS_Total/Riaru_Total/RSport_Total/pages.html',
            LiveinternetPagesController::SITE_TASS          => 'https://www.liveinternet.ru/stat/TASS_total/pages.html',
            LiveinternetPagesController::SITE_AIF           => 'https://www.liveinternet.ru/stat/AIF/pages.html',
            LiveinternetPagesController::SITE_RG_RU         => 'https://www.liveinternet.ru/stat/rg.ru/pages.html',
            LiveinternetPagesController::SITE_KOMMERSANT    => 'https://www.liveinternet.ru/stat/kommersant.ru/pages.html',
            LiveinternetPagesController::SITE_ECHO_MSK      => 'https://www.liveinternet.ru/stat/echo.msk.ru/pages.html',
            LiveinternetPagesController::SITE_5_TV          => 'https://www.liveinternet.ru/stat/5-tv.ru/pages.html',
            LiveinternetPagesController::SITE_INOSMI        => 'https://www.liveinternet.ru/stat/inosmi.ru/pages.html',
        ];

        $titles = [
            LiveinternetPagesController::SITE_KOMMERSANT_TITLES => 'https://www.liveinternet.ru/stat/kommersant.ru/titles.html',
            LiveinternetPagesController::SITE_REN_TV            => 'https://www.liveinternet.ru/stat/ren.tv/titles.html',
            LiveinternetPagesController::SITE_VEDOMOSTI         => 'https://www.liveinternet.ru/stat/vedomosti.ru/titles.html',
            LiveinternetPagesController::SITE_TVRAIN            => 'https://www.liveinternet.ru/stat/tvrain.ru/titles.html',
            LiveinternetPagesController::SITE_3DNEWS            => 'https://www.liveinternet.ru/stat/3dnews.ru/titles.html',
            LiveinternetPagesController::SITE_DOCTORPITER_RU    => 'https://www.liveinternet.ru/stat/doctorpiter.ru/titles.html',
            LiveinternetPagesController::SITE_AIF_HEALTH        => 'https://www.liveinternet.ru/stat/aif.ru/health/titles.html',
            LiveinternetPagesController::SITE_AIF_SOCIETY       => 'https://www.liveinternet.ru/stat/aif.ru/society/titles.html',
            LiveinternetPagesController::SITE_RIA_PRIME         => 'https://www.liveinternet.ru/stat/RS_Total/RS_projects/1prime_Total/titles.html',
            LiveinternetPagesController::SITE_FOREXPF_RU        => 'https://www.liveinternet.ru/stat/forexpf.ru/titles.html',
            LiveinternetPagesController::SITE_ANEKDOT_RU        => 'https://www.liveinternet.ru/stat/anekdot.ru/titles.html',
        ];

        return [$pages,$titles];
    }


    private function extractRows($html){
        $html   = substr($html,strpos($html,'по месяцам'));
        $html   = substr($html,strpos($html,'<table'));
        $html   = substr($html,0,strpos($html,'</table'));
        $rows   = explode('<tr',$html);

        return $rows;
    }

    private function extractLink($row){
        $start  = strpos($row,'href="');
        if(!$start){
            return '';
        }

        $link   = substr($row,$start + 6);
        $link   = substr($link,0,strpos($link,'"'));
        return $link;
    }

    private function extractViews($row){
        $views  = substr($row,strpos($row,'</a></label>'));
        $views  = substr($views,strpos($views,'<td>')+4);
        $views  = substr($views,0,strpos($views,'</td>'));
        $views  = intval(str_replace(',','',$views));

        return $views;
    }

    private function extractViews2($row){
        $views  = substr($row,strpos($row,'</label>'));
        $views  = substr($views,strpos($views,'<td>')+4);
        $views  = substr($views,0,strpos($views,'</td>'));
        $views  = intval(str_replace(',','',$views));

        return $views;
    }

    private function extractTitle($row){
        $start  = strpos($row,'<label for="id_');
        if(!$start){
            return '';
        }

        $title  = substr($row,$start + 15);
        if(in_array(substr($title,0,1),['c','t'])){
            return '';
        }
        $title  = substr($title,strpos($title,'">')+2 );
        $title  = substr($title,0,strpos($title,'</label'));
        return $title;
    }
}
