<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ErrorController extends Controller
{
    public function error($id)
    {
        return view('errors.'.$id);
    }
    // public function error503()
    // {
    //     return view('errors.503');
    // }
}
