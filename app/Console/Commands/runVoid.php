<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class runVoid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:void {job=0} {--t|time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запускает задачу в очередь без параметров';

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
     * @return mixed
     */
    public function handle()
    {
        $job    = $this->argument('job');

        if($job){
            $time   = $this->option('time');
            if($time){
                echo date("Y-m-d H:i:s\n");
            }
            $class  = "\App\Jobs\\$job";
            dispatch( new $class());
            if($time){
                echo date("Y-m-d H:i:s\n");
            }
        }else{
            echo "Не указана job-класс\n";
        }
    }
}
