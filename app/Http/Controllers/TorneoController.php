<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Torneo;
use App\Models\Person;
use App\Models\Ciudad;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TorneoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create()
    {
        $this->custom_authorize('add_torneos');
        $people = Person::whereNull('deleted_at')->get();
        $ciudades = Ciudad::whereNull('deleted_at')->get();
        $dataTypeContent = new Torneo(); // Objeto vacío para la vista
        return view('torneos.edit-add', compact('people', 'ciudades', 'dataTypeContent'));
    }

    public function store(Request $request)
    {
        $this->custom_authorize('add_torneos');
        $request->validate([
            'nombre' => 'required|string|max:255',
            'person_id' => 'required|exists:people,id',
            'ciudad_id' => 'required|exists:ciudads,id',
            'fechainicio' => 'required|date',
            'fechafinal' => 'required|date|after_or_equal:fechainicio',
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);

        try {
            $data = $request->all();

            if ($request->hasFile('archivo')) {
                $data['archivo'] = $request->file('archivo')->store('torneos', 'public');
            }

            Torneo::create($data);

            return redirect()->route('voyager.torneos.index')
                ->with(['message' => 'Torneo creado exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function edit($id)
    {
        $this->custom_authorize('edit_torneos');
        $dataTypeContent = Torneo::findOrFail($id);
        $people = Person::whereNull('deleted_at')->get();
        $ciudades = Ciudad::whereNull('deleted_at')->get();
        return view('torneos.edit-add', compact('dataTypeContent', 'people', 'ciudades'));
    }

    public function update(Request $request, $id)
    {
        $this->custom_authorize('edit_torneos');
        $request->validate([
            'nombre' => 'required|string|max:255',
            'person_id' => 'required|exists:people,id',
            'ciudad_id' => 'required|exists:ciudads,id',
            'fechainicio' => 'required|date',
            'fechafinal' => 'required|date|after_or_equal:fechainicio',
            'archivo' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
        ]);

        try {
            $torneo = Torneo::findOrFail($id);
            $data = $request->all();

            if ($request->hasFile('archivo')) {
                // Eliminar archivo anterior si existe
                if ($torneo->archivo) {
                    Storage::disk('public')->delete($torneo->archivo);
                }
                $data['archivo'] = $request->file('archivo')->store('torneos', 'public');
            }

            $torneo->update($data);

            return redirect()->route('voyager.torneos.index')
                ->with(['message' => 'Torneo actualizado exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }
}
