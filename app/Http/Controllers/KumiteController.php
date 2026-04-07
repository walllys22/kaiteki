<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KumiteController extends Controller
{
    public function index()
    {
        return view('kumite_temporizador');
    }

    // App/Http/Controllers/KumiteController.php o Livewire Component

public function determinarGanador($c1, $c2) {
    // $c1 y $c2 son objetos con: nombre, dojo, puntos, senshu, tecnicas (ippon, wazaari, yuko)
    
    // 1. Ganador por mayoría
    if ($c1->puntos > $c2->puntos) return $c1;
    if ($c2->puntos > $c1->puntos) return $c2;

    // 2. Empate: Senshu
    if ($c1->senshu) return $c1;
    if ($c2->senshu) return $c2;

    // 3. Empate con puntos (Sin Senshu): Técnica más alta
    // Suponiendo que guardas conteo de Ippon (3pts), Waza-ari (2pts)
    if ($c1->puntos > 0 && $c2->puntos > 0) {
        if ($c1->ippon > $c2->ippon) return $c1;
        if ($c2->ippon > $c1->ippon) return $c2;
        if ($c1->wazaari > $c2->wazaari) return $c1;
        if ($c2->wazaari > $c1->wazaari) return $c2;
    }

    // 4. Empate 0-0 o empate técnico absoluto
    // Retornar null para activar los botones de decisión manual (Hantei)
    return null; 
    }
}