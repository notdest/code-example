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
    protected $signature = 'Instagram:subscribe {limit=50}';

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
        $limit      = $this->argument('limit');

        $config     = new \App\Configs\Instagram();

        if ($config->enabled == 0){
            return 0;
        }

        $instagram  = $config->getClient();
        sleep(2);

        $account    = $instagram->getAccount($config->login);
        sleep(1);

        try {
            $subscribed = $instagram->getFollowing($account->getId(),500);  // решил, что 500 подписок достаточно
        }catch (InstagramException $e){
            $this->err( "could not get followings");
            return 0;
        }


        $usernames   = array_map(function ($v){ return $v['username'];},$subscribed['accounts'] );

        $sources  = DB::select("SELECT * FROM `sources` WHERE `type`='instagram' AND `active` > 0;");

        echo "Total sources (with subscribed):".count($sources)."   limit: $limit\n\n";

        $i = 0;
        foreach ($sources as $source){
            if(!in_array($source->code,$usernames)){
                echo "processed: $i\r";

                if ($i >= $limit){
                    echo "\n\nlimit reached: $limit\n";
                    $config->dropErrors();
                    return 0;
                }

                try {
                    $account = $instagram->getAccount($source->code);
                    $instagram->follow($account->getId());

                    sleep(rand(1,5));
                    $i++;
                }catch (InstagramNotFoundException $e){
                    $this->call('Instagram:deactivate', [ 'id' => $source->id ]);
                }catch (InstagramException $e){
                    $this->err( "Unknown error: https://www.instagram.com/{$source->code}/\n".
                                "command for deactivate: ./artisan Instagram:deactivate $source->id");
                    return 0;
                }
            }
        }

        $config->dropErrors();
        $config->enableSubscription = 0;        // если мы не достигли лимита и оказались здесь
        $config->save();                        // то можно выключать парсинг

        return 0;
    }

    private function err($msg){
        fwrite(STDERR, "\n\n$msg\n\n");
    }
}
