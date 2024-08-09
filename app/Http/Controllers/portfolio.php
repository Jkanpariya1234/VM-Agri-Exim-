<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class portfolio extends Controller
{
    function index(){
        return view('portfolio-details');
    }
}
