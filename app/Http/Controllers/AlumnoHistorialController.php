<?php

namespace App\Http\Controllers;
use App\Models\Alumno;
use App\Models\Grado;
use App\Models\alumnoHistoriale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class AlumnoHistorialController extends Controller
{
    /**
     * Muestra el historial del alumno.
     */
    public function show($id)
    {
        $this->custom_authorize('read_alumnos');

        $dataTypeContent = Alumno::with(['person', 'horario', 'grado'])->findOrFail($id);
        $grado = Grado::whereNull('deleted_at')->get();
        $historial = alumnoHistoriale::whereNull('deleted_at')->get();
        return view('alumnos.historial.read', compact('grado', 'historial', 'dataTypeContent'));
    }



}
