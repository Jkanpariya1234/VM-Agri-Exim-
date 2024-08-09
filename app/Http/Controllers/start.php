<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class start extends Controller
{
    function index(){
        return view('starter-page');
    }
}
