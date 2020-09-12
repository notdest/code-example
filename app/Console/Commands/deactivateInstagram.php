<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class deactivateInstagram extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Instagram:deactivate {id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Деактивирует аккаунт Instagram с указанным Id';

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
        $id     = $this->argument('id');

        if($id){
            $source         = \App\Source::find($id);
            $source->active = 0;
            $source->save();
            $this->line("<fg=green>Instagram $source->code was deactivated</>");
        }else{
            $this->line( "<fg=red> no id specified </>\n");
            $this->line("<fg=green>./artisan Instagram:deactivate 0000</>");
        }

        return 0;
    }
}
