<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use InstagramScraper\Exception\{InstagramNotFoundException,InstagramException};

class subscribeInstagram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Instagram:subscribe {limit=50} {--p|progress}';

    private $instagram;
    private $processed  = 0;
    private $progress   = false;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Подписывается на нужные аккаунты в Instagram';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $limit          = $this->argument('limit');
        $this->progress = $this->option("progress");    // показывать ли прогресс

        $config     = new \App\Configs\Instagram();

        if ($config->enabled == 0){
            return 0;
        }

        $this->instagram  = $config->getClient();
        sleep(2);

        $alreadyFollowings  = $this->followings($config->login); // подписки, в одной "желаемые", в другой - "действительные"
        $requiredFollowings = DB::table('sources')->where('type','=','instagram')->where('active','>','0')
                                                                                 ->pluck('id','code')->all();

        if((count($alreadyFollowings)+$limit)%20 === 0){ // Этот костыль нужен из-за того, что библиотека вылетает, когда
            $limit -= 1;                                // подписок кратно 20
        }

        foreach ($alreadyFollowings as $code){                      // сналчала удаляем те, которые есть, но не должны быть
            if(!isset($requiredFollowings[$code])){
                $this->output("processed: {$this->processed}\r");
                $this->checkLimit($limit,$config);
                $this->unfollow($code);
            }
        }

        $this->output("Total sources (with subscribed):".count($requiredFollowings)."   limit: $limit\n\n");
        foreach ($requiredFollowings as $code => $id){              // потом добавляем те, которых нет но должны быть
            if(!in_array($code,$alreadyFollowings)){
                $this->output("processed: {$this->processed}\r");
                $this->checkLimit($limit,$config);
                $this->follow($code,$id);
            }
        }

        $config->dropErrors();
        $config->enableSubscription = 0;        // если мы не достигли лимита и оказались здесь
        $config->save();                        // то можно выключать парсинг

        return 0;
    }





    private function output($text){
        if($this->progress){
            echo $text;
        }
    }

    private function checkLimit($limit,$config){
        if ($this->processed >= $limit){
            $this->output("\n\nlimit reached: $limit\n");
            $config->dropErrors();
            exit(0);
        }
    }

    // подписывается на акк по коду, или удаляет его по id в нашей базе
    private function follow($code,$id){
        try {
            $account = $this->instagram->getAccount($code);
            $this->instagram->follow($account->getId());

            sleep(rand(1,5));
            $this->processed++;
        }catch (InstagramNotFoundException $e){
            $this->call('Instagram:deactivate', [ 'id' => $id ]);
        }catch (InstagramException $e){
            $this->err( "Unknown error: https://www.instagram.com/{$code}/\n".
                "command for deactivate: ./artisan Instagram:deactivate $id");
            exit(1);
        }
    }

    private function unfollow($code){
        $account = $this->instagram->getAccount($code);
        $this->instagram->unfollow($account->getId());

        sleep(rand(1,5));
        $this->processed++;
    }

    // возвращает массив с юзернеймами тех, на кого подписан аккаунт
    private function followings($login){
        $account    = $this->instagram->getAccount($login);
        sleep(1);

        try {
            $subscribed = $this->instagram->getFollowing($account->getId(),500);  // решил, что 500 подписок достаточно
        }catch (InstagramException $e){
            $this->err( "could not get followings");
            exit(1);
        }

        return  array_map(function ($v){ return $v['username'];},$subscribed['accounts'] );
    }

    private function err($msg){
        fwrite(STDERR, "\n\n$msg\n\n");
    }
}
