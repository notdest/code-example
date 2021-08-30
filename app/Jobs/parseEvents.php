<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class parseEvents implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $httpClient = new \GuzzleHttp\Client();
        $response   = $httpClient->request('GET', 'https://setters.agency/calendar');
        $html       = $response->getBody()->getContents();

        $events     = $this->extractEvents($html);
        $categories = $this->extractCategories($html);
        foreach ($categories as $category) {
            if(!isset(\App\Event::$categories[intval($category->id)])){
                throw new \Exception('Added a new category on the site');
            }elseif( $category->name !== \App\Event::$categories[intval($category->id)]){
                throw new \Exception('The category has been renamed on the site');
            }
        }

        $endLimit   = strtotime('-2 day');
        foreach ($events as $event) {
            if(strlen($event->end)>0){
                $end = strtotime($event->end);
            }else{
                $end = strtotime($event->start);
            }
            if($end < $endLimit){
                continue;
            }

            \App\Event::firstOrCreate(
                ['title'    => $event->title,
                 'end'      => date('Y-m-d 23:59:59',$end+(25*3600))],   // индекс в этом поле. И у них всё на день раньше

                ['category' => intval($event->categoryId),
                 'start'    => date('Y-m-d 00:00:00',strtotime($event->start)+(25*3600))]
            );
        }
    }

    private function extractEvents($html){
        $json = substr($html,strpos($html,'calendarDates = [') + 16);
        $json = substr($json,0,strpos($json,'];')+1);
        $json = str_replace(    [' id:',  ' categoryId:',  ' title:',  ' start:',  ' end:'],
                                [' "id":',' "categoryId":',' "title":',' "start":',' "end":'],$json);
        $json = preg_replace('/\},\s+\]/','}]',$json);

        $events = json_decode($json);
        if(is_null($events)){
            throw new \Exception('Invalid events JSON ');
        }
        return $events;
    }

    private function extractCategories($html){
        $json = substr($html,strpos($html,'const calendarCategoreis = [') + 27);
        $json = substr($json,0,strpos($json,'];')+1);
        $json = str_replace(    [' id:',  ' name:',  ' bgColor:'],
                                [' "id":',' "name":',' "bgColor":'],$json);
        $json = preg_replace('/\},\s+\]/','}]',$json);

        $categories = json_decode($json);
        if(is_null($categories)){
            throw new \Exception('Invalid categories JSON ');
        }
        return $categories;
    }
}
