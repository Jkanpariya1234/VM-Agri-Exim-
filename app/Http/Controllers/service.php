<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class service extends Controller
{
    function index(){
        return view('service-details');
    }
}
