<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class topMailRu implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->client   = new \GuzzleHttp\Client();

        $pages      = [
            'https://top.mail.ru/Rating/Cars/Today/Visitors/'           =>[ 'www.zr.ru'         ],
            'https://top.mail.ru/Rating/Business/Today/Visitors/'       =>[ 'www.bfm.ru'        ],
            'https://top.mail.ru/Rating/Culture/Today/Visitors/'        =>[ 'www.kino-teatr.ru',
                                                                            'www.vokrug.tv'     ],
            'https://top.mail.ru/Rating/Science/Today/Visitors/'        =>[ 'hi-tech.mail.ru'   ],
            'https://top.mail.ru/Rating/Computers-News/Today/Visitors/' =>[ 'www.iguides.ru'    ],
            'https://top.mail.ru/Rating/House/Today/Visitors/'          =>[ 'russianfood.com',
                                                                            'www.ogorod.ru'     ],
        ];

        foreach ($pages as $page => $sites) {
            $html   = $this->download($page);
            sleep(1);
            $rows   = $this->extractRows($html);
            $date   = $this->extractDate($html);

            foreach ($sites as $site) {
                $row            = $this->find($rows,$site);
                [$users,$views] = $this->extractData($row);
                $this->saveData($site,$date,$users,$views);
            }
        }
    }


    private function saveData($site,$date,$users,$views){
        \DB::table('top_mail_ru')->insert([
            'site'  => $site,
            'date'  => $date,
            'users' => $users,
            'views' => $views,
        ]);
    }

    private function extractData($row){
        preg_match_all('~<td class="it-number">(.*)</td>~sU',$row,$m);
        $data   = $m[1];

        $users  = substr($data[0],0,strpos($data[0],'<br />'));
        $users  = trim( strip_tags( str_replace(',','',$users) ) );
        $users  = intval($users);

        $views  = substr($data[1],0,strpos($data[1],'<br />'));
        $views  = trim( str_replace(',','',$views) );
        $views  = intval($views);

        return [$users,$views];
    }

    private function extractDate($html){
        $html   = iconv('CP1251','UTF-8',$html);
        $html   = substr($html,strpos($html,'обновлён')+20);
        $html   = substr($html,0,strpos($html,'</b>'));
        $time   = strtotime($html.':00');
        return date('Y-m-d',$time);
    }

    private function find($rows,$site){
        foreach ($rows as $row) {
            if(strpos($row,$site)){
                return $row;
            }
        }
        return '';
    }

    private function extractRows($html){
        $html   = substr($html,strpos($html,'<tr class="ReportTable-TRow">'));
        preg_match_all('~<tr class="ReportTable-TRow">(.*)</tr>~sU',$html,$m);
        return $m[1];
    }

    private function download($page){
        $response   = $this->client->request('GET', $page,[
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.60 Safari/537.36',
            ]
        ]);
        return $response->getBody()->getContents();
    }
}
