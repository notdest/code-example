<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{

    public function index(){
        $posts  = DB::table('posts')
                    ->join('sources', 'posts.sourceId', '=', 'sources.id')
                    ->join('persons', 'sources.personId', '=', 'persons.id')
                    ->paginate(15);


        return view('posts.index',[
            'posts' => $posts,
        ]);
    }
}
