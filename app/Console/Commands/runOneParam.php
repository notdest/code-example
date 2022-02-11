<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class runOneParam extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:oneParam {job} {first} {--t|time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Запускает задачу в очередь с одним параметром';

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
        $time   = $this->option('time');

        if($time){
            echo date("Y-m-d H:i:s\n");
        }

        $class  = "\App\Jobs\\$job";
        dispatch( new $class($first));

        if($time){
            echo date("Y-m-d H:i:s\n");
        }

        return Command::SUCCESS;
    }
}
