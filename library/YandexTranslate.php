<?php




class YandexTranslate {



    // Берет на вход массив с текстами, на выходе точно такой же массив с переводами
    public static function batch($batch){
        $key        = env('IMEDIA_YANDEX_CLOUD_KEY');

        $client     = new \GuzzleHttp\Client(['base_uri' => 'https://translate.api.cloud.yandex.net']);

        $data       = [ "targetLanguageCode"    => 'ru',
                        "texts"                 => $batch];

        $headers    = [ 'Authorization'  => 'Api-Key '.$key ];

        $response   = $client->request('POST', '/translate/v2/translate',[ 'json' => $data, 'headers'  => $headers ]);
        $result     = json_decode($response->getBody()->getContents());

        $translated = [];
        foreach ($result->translations as $k => $translation) {
            $translated[$k] = $translation->text;
        }

        return $translated;
    }


    // на вход текст, на выходе строка с переводом
    public static function single($text){
        $res    = self::batch([$text]);
        return reset($res);
    }

    // У Яндекса ограничение 20000 символов на запрос и ответ, это 10000 на запрос
    public static function large($text){
        $limit  = 8000;

        $chunks = [];
        while(strlen($text) > $limit){
            $chunk  = substr($text,0,$limit);

            $pos1   = strrpos($chunk,'.') + 1;
            $pos2   = strrpos($chunk,"\n") + 1;
            $pos    = max($pos1,$pos2);

            $chunk  = substr($text,0,$pos);
            $text   = substr($text,$pos);
            $chunks[]   = $chunk;
        }
        $chunks[] = $text;

        $translated = '';
        foreach ($chunks as $chunk) {
            $translated .= self::single($chunk);
        }

        return $translated;
    }
}
