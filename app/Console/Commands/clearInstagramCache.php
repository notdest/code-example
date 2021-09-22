<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class clearInstagramCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Instagram:clearCache {months=6}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Удаляет посты и картинки старше указанных месяцев';

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
        $cacheDir   = '/var/www/public/img_cache/';
        $months     = $this->argument('months');

        $excludedDirs   = ['.','..'];                                           // Сначала удаляем старые файлы
        if($months > 0){
            $excludedDirs[] = date("Y_m");
            for ($i = 1 ;$i < $months ;$i++){
                $excludedDirs[] = date("Y_m",strtotime("-$i month"));
            }
        }

        $dirs   = scandir($cacheDir);
        foreach ($dirs as $dir) {
            if(!in_array($dir,$excludedDirs)){
                if(is_dir($cacheDir.$dir)){
                    if(!File::deleteDirectory($cacheDir.$dir.'/')){
                        throw new \Exception('I can not delete the folder '.$cacheDir.$dir);
                    }
                }
            }
        }

                                                                                // Потом удаляем старые посты
        DB::table('posts')->where('createdTime','<',date("Y-m-1 00:00:00",strtotime("-".($months-1)." month")))->delete();
        DB::table('stories')->where('createdTime','<',date("Y-m-1 00:00:00",strtotime("-".($months-1)." month")))->delete();

        return 0;
    }
}
