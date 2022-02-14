<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class runTwoParam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:twoParam {job=0} {first?} {second?} {--t|time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запускает задачу в очередь с двумя параметрами';

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
        $job    = $this->argument('job');
        $first  = $this->argument('first');
        $second = $this->argument('second');

        if($job && !is_null($first) && !is_null($second)){
            $time   = $this->option('time');
            if($time){
                echo date("Y-m-d H:i:s\n");
            }
            $class  = "\App\Jobs\\$job";
            dispatch( new $class($first,$second));
            if($time){
                echo date("Y-m-d H:i:s\n");
            }
        }else{
            echo "Переданы не все параметры\n";
        }

        return Command::SUCCESS;
    }
}
