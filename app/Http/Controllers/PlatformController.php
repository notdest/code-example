<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlatformController extends Controller
{

    public function alphabet(){
        return view('platforms.alphabet');
    }

    public function list(){
        return view('platforms.list');
    }
}
