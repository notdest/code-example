<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class LiveinternetAdditional implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $date;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($input){
        $this->date     = date('Y-m-d',strtotime($input));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $general    = [
            'maximonline'       => 'https://www.liveinternet.ru/stat/hsdigital/mn/maximonline/index.html',
            'championat_com'    => 'https://www.liveinternet.ru/stat/championat_com/index.html',
            'sport-express.ru'  => 'https://www.liveinternet.ru/stat/sport-express.ru/index.html',
            'Lenta'             => 'https://www.liveinternet.ru/stat/Lenta/index.html',
            'RBCRU'             => 'https://www.liveinternet.ru/stat/RBCRU/index.html',
            'kommersant.ru'     => 'https://www.liveinternet.ru/stat/kommersant.ru/index.html',
            'moslenta.ru'       => 'https://www.liveinternet.ru/stat/moslenta.ru/index.html',
            'vokrugsveta.ru'    => 'https://www.liveinternet.ru/stat/vokrugsveta.ru/index.html',
            'sobaka.ru'         => 'https://www.liveinternet.ru/stat/sobaka.ru/index.html',
            'kp'                => 'https://www.liveinternet.ru/stat/kp/index.html',
            'woman.ru'          => 'https://www.liveinternet.ru/stat/woman.ru/index.html',
            'starhit.ru'        => 'https://www.liveinternet.ru/stat/starhit.ru/index.html',
            'marieclaire.ru'    => 'https://www.liveinternet.ru/stat/marieclaire.ru/index.html',
            'psychologies.ru'   => 'https://www.liveinternet.ru/stat/psychologies.ru/index.html',
            'elle.ru'           => 'https://www.liveinternet.ru/stat/elle.ru/index.html',
        ];

        $ru_media   = [     // date сразу запихал в урлы
            'general'           => 'https://www2.liveinternet.ru/stat/ru/media/index.html?date=',
            's_yandex'          => 'https://www2.liveinternet.ru/stat/ru/media/index.html?slice=s_yandex&date=',
            's_googl'           => 'https://www2.liveinternet.ru/stat/ru/media/index.html?slice=s_googl&date=',
            'zen'               => 'https://www2.liveinternet.ru/stat/ru/media/index.html?slice=zen&date=',
            'android_google'    => 'https://www2.liveinternet.ru/stat/ru/media/index.html?slice=android-google&date=',
            'social'            => 'https://www2.liveinternet.ru/stat/ru/media/index.html?slice=social&date=',
            'n_y'               => 'https://www2.liveinternet.ru/stat/ru/media/index.html?slice=n_y&date=',
        ];

        foreach ($general as $site => $url) {
            $html               = $this->download("$url?date=$this->date");
            [$views, $users]    = $this->getData($html);

            DB::table('liveinternet')->insert([
                'type'      => 'general',
                'site'      => $site,
                'day'       => $this->date,
                'views'     => $views,
                'users'     => $users,
            ]);
        }

        foreach ($ru_media as $type => $url) {
            $html               = $this->download($url.$this->date);
            [$views, $users]    = $this->getData($html);

            DB::table('liveinternet')->insert([
                'type'      => $type,
                'site'      => 'ru_media',
                'day'       => $this->date,
                'views'     => $views,
                'users'     => $users,
            ]);
        }
    }


    private function getData($html){
        preg_match('~<label for="id_0">.*<tr~smU',$html,$m);
        $tr     = $m[0];
        preg_match('~<td>(.*)</td>~',$tr,$m);
        $views  = intval(str_replace(',','',$m[1]));

        preg_match('~<label for="id_8">.*<tr~smU',$html,$m);
        $tr     = $m[0];
        preg_match('~<td>(.*)</td>~',$tr,$m);
        $users  = intval(str_replace(',','',$m[1]));

        return [$views,$users];
    }


    private function download($url){
        static $client;
        if(!$client){
            $client = new \GuzzleHttp\Client();
        }

        $response   = $client->request('GET',$url);
        sleep(1);
        return $response->getBody()->getContents();
    }
}
