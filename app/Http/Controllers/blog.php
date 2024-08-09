<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Blog as BlogModel;

class blog extends Controller
{
    public function index()
    {
        $blogs = BlogModel::all();
        return view('blog', compact('blogs'));
    }
}
