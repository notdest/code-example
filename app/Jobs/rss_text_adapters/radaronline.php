<?php
namespace App\Jobs\rss_text_adapters;


class radaronline {

    protected $client;

    public function __construct($client){
        $this->client   = $client;
    }

    public function download($url){
        $html   = strval($this->client->get($url)->getBody());

        $pos    = strpos($html,'"articleBody":"');
        if($pos === false){
            throw new \Exception('Can\'t found "articleBody" - '.$url);
        }
        $html   = substr($html,$pos+15);

        $pos    = strpos($html,'","');
        if($pos === false){
            throw new \Exception('Can\'t found "," - '.$url);
        }
        $html   = substr($html,0,$pos);

        $html   = stripslashes($html);

        return $html;
    }
}
