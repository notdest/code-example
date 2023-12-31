<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class parseLiveinternet implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;
    protected $nextDate;
    protected $append;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct( $input, $append = false ){
        $time           = strtotime($input);
        $this->date     = date('Y-m-d',$time);
        $this->nextDate = date('Y-m-d',strtotime('+1 day',$time));
        $this->append   = (bool) $append;
    }


    public function middleware(){
        return [(new WithoutOverlapping())->dontRelease()->expireAfter(600)];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(){
        $sources    = [
            'zen'               => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=zen',
            'social'            => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=social',
            'yandex'            => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=s_yandex',
            'ru'                => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=ru',
            's_googl'           => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=s_googl',
            'n_y'               => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=n_y',
            'n_g'               => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=n_g',
            'android_google'    => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=android-google',
            'smi2'              => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=smi2',
            'vk'                => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=vk',
            'm.vk.com'          => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=m.vk.com',
            'facebook'          => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=facebook',
            'm.facebook.com'    => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=m.facebook.com',
            'ok'                => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=ok',
            'n-m'               => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=n-m',
            's_mail'            => 'https://www.liveinternet.ru/stat/ru/media/servers.html?slice=s_mail',
        ];

        if($this->append){
            $collected  = DB::table('liveinternet')->select('type')->distinct()->where('day', $this->date)
                                                                               ->get()->keyBy('type')->all();
        }else{
            $count  =  DB::table('liveinternet')->where('day', $this->date)->count();
            if($count > 0){
                throw new \Exception('already processed this date');
            }
        }

        $httpClient = new \GuzzleHttp\Client();
        $jar        = \GuzzleHttp\Cookie\CookieJar::fromArray(['per_page' => '100'], 'www.liveinternet.ru');

        foreach ($sources as $type => $link) {
            if(isset($collected[$type])) continue;

            sleep(1);
            $response   = $httpClient->request('GET', $link.'&date='.$this->date, ['cookies' => $jar]);
            $html       = $response->getBody()->getContents();

            if(!strpos($html,"date={$this->nextDate}\">")){         // ссылка "далее" будет вести на другую дату
                throw new \Exception('date less than minimum');     // если мы смотрим даты, которых уже нет
            }

            $rows       = $this->extractRows($html);

            $insertion  = [];
            foreach ($rows as $row) {
                $name   = $this->extractName($row);
                if(!$name) continue;

                $views          = $this->extractViews($row);
                $insertion[]    = [ 'type' => $type, 'site' => $name, 'day' =>  $this->date, 'views' => $views];
            }

            DB::table('liveinternet')->insert($insertion);
        }
    }


    private function extractViews($row){
        $views  = substr($row,strpos($row,'</a></label>'));
        $views  = substr($views,strpos($views,'<td>')+4);
        $views  = substr($views,0,strpos($views,'</td>'));
        $views  = intval(str_replace(',','',$views));

        return $views;
    }

    private function extractName($row){
        $start  = strpos($row,'href="/stat/');
        if(!$start){
            return '';
        }
        $name   = substr($row,$start + 12);
        $name   = substr($name,0,strpos($name,'/"'));
        return $name;
    }

    private function extractRows($html){
        $html   = substr($html,strpos($html,'по месяцам'));
        $html   = substr($html,strpos($html,'<table'));
        $html   = substr($html,0,strpos($html,'</table'));
        $rows   = explode('<tr',$html);

        return $rows;
    }
}
