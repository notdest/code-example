<?php
namespace App\Jobs\rss_text_adapters;


class cosmopolitan_com {

    protected $client;

    public function __construct($client){
        $this->client   = $client;
    }



    public function download($url){
        $html   = strval($this->client->get($url)->getBody());



        $pos    = strpos($html,'<div class="article-body-content');
        if($pos === false){
            $pos    = strpos($html,'<h1');
        }
        if($pos === false){
            throw new \Exception('Can\'t found "article-body-content" - '.$url);
        }
        $html   = substr($html,$pos);



        $pos    = strpos($html,'<div class="embed embed-editorial-links embed-center"');
        if($pos === false){
            $pos    = strpos($html,'<div class="authors ">');
        }
        if($pos === false){
            $pos    = strpos($html,'<div class="screen-reader-only">');
        }

        if($pos === false){
            throw new \Exception('Can\'t found "embed-editorial-links" - '.$url);
        }

        $html   = substr($html,0,$pos);

        $html   = strip_tags($html);
        $html   = html_entity_decode($html);
        $html   = str_replace("\t",'',$html);
        $html   = preg_replace('/\n{3,}/',"\n\n",$html);
        $html   = trim($html);

        return $html;
    }
}
