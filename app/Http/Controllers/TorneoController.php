<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Torneo;
use App\Models\Person;
use App\Models\Ciudad;
use App\Models\Categoria;
use App\Models\Modalida;
use App\Models\TorneoCategoria;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TorneoController extends Controller
{
    protected $storageController;

    public function __construct()
    {
        $this->middleware('auth');
        $this->storageController = new StorageController();
    }

    public function index()
    {
        $this->custom_authorize('browse_torneos');
        return view('torneos.browse');
    }

    public function list(){

        $search = request('search') ?? null;
        $paginate = request('paginate') ?? 10;

        $data = Torneo::query()
            // El método when() hace lo mismo que tu "if($search)"
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('id', $search)
                      ->orWhere('nombre', 'like', "%$search%");
                });
            })
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->paginate($paginate);

        return view('torneos.list', compact('data'));
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
                $file = $request->file('archivo');
                // Si el archivo es una imagen, usamos el StorageController para optimizarla
                if (str_starts_with($file->getMimeType(), 'image/')) {
                    $data['archivo'] = $this->storageController->store_image($file, 'torneos');
                } else {
                    // Si es un PDF u otro tipo de archivo, usamos el guardado tradicional
                    $data['archivo'] = $file->store('torneos', 'public');
                }
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
                
                $file = $request->file('archivo');
                // Aplicamos la misma lógica de detección para la actualización
                if (str_starts_with($file->getMimeType(), 'image/')) {
                    $data['archivo'] = $this->storageController->store_image($file, 'torneos');
                } else {
                    $data['archivo'] = $file->store('torneos', 'public');
                }
            }

            $torneo->update($data);

            return redirect()->route('voyager.torneos.index')
                ->with(['message' => 'Torneo actualizado exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function show($id)
    {
        $this->custom_authorize('read_torneos');
        $dataTypeContent = Torneo::with(['ciudad', 'person'])->findOrFail($id);
        $categorias = Categoria::whereNull('deleted_at')->orderBy('nombre')->get();
        $modalidades = Modalida::whereNull('deleted_at')->orderBy('nombre')->get();

        return view('torneos.read', compact('dataTypeContent', 'categorias', 'modalidades'));
    }

    public function categoryList($id)
    {
        $search = request('search');
        $paginate = request('paginate') ?? 10;

        $data = TorneoCategoria::with(['categoria', 'modalidad'])
            ->where('torneo_id', $id)
            ->when($search, function ($query, $search) {
                return $query->whereHas('categoria', function($q) use ($search) {
                    $q->where('nombre', 'like', "%$search%");
                })->orWhereHas('modalidad', function($q) use ($search) {
                    $q->where('nombre', 'like', "%$search%");
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate($paginate);

        return view('torneos.categories.list', compact('data', 'id'));
    }

    public function categoryStore(Request $request)
    {
        try {
            TorneoCategoria::create([
                'torneo_id' => $request->torneo_id,
                'categoria_id' => $request->categoria_id,
                'modalidad_id' => $request->modalidad_id
            ]);
            return response()->json(['success' => true, 'message' => 'Categoría agregada correctamente']);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }

    public function categoryDestroy($id)
    {
        try {
            $item = TorneoCategoria::findOrFail($id);
            $item->delete();
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            return response()->json(['success' => false, 'message' => $th->getMessage()], 500);
        }
    }
}
