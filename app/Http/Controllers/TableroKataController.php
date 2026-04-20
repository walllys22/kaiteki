<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kata;

class TableroKataController extends Controller
{
    public function index()
    {
        // Obtenemos los datos de la tabla katas para mostrarlos en el tablero
        return view('tablero_kata');
    }
}