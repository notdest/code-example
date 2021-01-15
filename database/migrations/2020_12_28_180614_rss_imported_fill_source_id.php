<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RssImportedFillSourceId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $sourcesRaw = DB::select('SELECT * FROM `rss_sources`;');
        $sources    = [];
        foreach ($sourcesRaw as $source){                        // получаем словарь источников 'адрес' => 'id'
            preg_match('|://([^/]+)/|',$source->link,$match);
            if(isset($match[1])){
                $sources[$match[1]] = $source->id;
            }
        }


        $limit  = 100;
        $offset = 0;

        do{
            $sql    = 'SELECT `id`,`source_id`,`source` FROM `rss_imported` ORDER BY `id` LIMIT ? OFFSET ? ;';
            $items  = DB::select( $sql, [$limit, $offset]);

            foreach ($items as $item){
                if(is_null($item->source_id)){
                    preg_match('|://([^/]+)/|',$item->source,$match);
                    if(isset($match[1]) && isset($sources[$match[1]])){
                        DB::table('rss_imported')->where('id', $item->id)->update(['source_id' => $sources[$match[1]] ]);
                    }
                }
            }

            $offset += $limit;
        }while(count($items)>0);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
