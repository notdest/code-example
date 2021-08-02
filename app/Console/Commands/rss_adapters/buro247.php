<?php
namespace App\Console\Commands\rss_adapters;

use Illuminate\Support\Carbon;

// мало отличается от common, может когда-нибудь следует слить
class buro247 extends common{

    public function filter($xml){
        $xml    = substr($xml,0,strpos($xml,'<turbo:analytics')) . substr($xml,strpos($xml,'turbo:analytics>')+16);

        return $xml;
    }
}
