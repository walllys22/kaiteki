<?php

namespace App\Http\Controllers;

use App\Models\Arancele;
use App\Models\Dojo;
use App\Models\Grado;
use Illuminate\Http\Request;

class GradoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->custom_authorize('browse_grados');

        return view('grados.browse');
    }

    public function list()
    {
        $search = request('search') ?? null;
        $paginate = request('paginate') ?? 10;

        $data = Grado::query()
            ->withCount('alumnoGrados')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('id', $search)
                        ->orWhere('tipo', 'like', "%$search%")
                        ->orWhere('numero', 'like', "%$search%")
                        ->orWhere('nombre', 'like', "%$search%")
                        ->orWhere('puntas', 'like', "%$search%")
                        ->orWhere('dias', 'like', "%$search%");
                });
            })
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->paginate($paginate);

        return view('grados.list', compact('data'));
    }

    public function show($id)
    {
        $this->custom_authorize('read_grados');

        $userDojoId = auth()->user()->dojo_id;

        $grado = Grado::query()
            ->whereNull('deleted_at')
            ->findOrFail($id);

        $dojos = Dojo::query()
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('id', $userDojoId);
            })
            ->orderBy('nombre')
            ->get();

        $aranceles = Arancele::query()
            ->with('dojo')
            ->where('grado_id', $grado->id)
            ->whereNull('deleted_at')
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->orderByDesc('status')
            ->orderBy('tipo')
            ->orderByDesc('created_at')
            ->get();

        return view('grados.read', compact('grado', 'dojos', 'aranceles', 'userDojoId'));
    }

    public function storeArancel(Request $request)
    {
        $this->custom_authorize('read_grados');

        $userDojoId = auth()->user()->dojo_id;

        $request->validate([
            'grado_id' => 'required|exists:grados,id',
            'dojo_id' => 'nullable|exists:dojos,id',
            'tipo' => 'required|in:Repaso,Examen',
            'precio' => 'required|numeric|min:0|max:99999999.99',
        ]);

        $dojoId = $userDojoId ?: $request->dojo_id;

        if (!$dojoId) {
            return redirect()->back()
                ->withInput()
                ->with(['message' => 'Debe seleccionar un dojo para registrar el arancel.', 'alert-type' => 'error']);
        }

        Dojo::query()
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('id', $userDojoId);
            })
            ->findOrFail($dojoId);

        $arancelExistente = Arancele::query()
            ->where('grado_id', $request->grado_id)
            ->where('dojo_id', $dojoId)
            ->where('tipo', $request->tipo)
            ->whereNull('deleted_at')
            ->first();

        if ($arancelExistente) {
            return redirect()->back()
                ->withInput()
                ->with(['message' => 'Ya existe un arancel de tipo ' . $request->tipo . ' para este grado y dojo.', 'alert-type' => 'error']);
        }

        Arancele::create([
            'grado_id' => $request->grado_id,
            'dojo_id' => $dojoId,
            'tipo' => $request->tipo,
            'precio' => $request->precio,
            'status' => 1,
        ]);

        return redirect()->route('voyager.grados.show', ['id' => $request->grado_id])
            ->with(['message' => 'Arancel registrado correctamente.', 'alert-type' => 'success']);
    }

    public function destroyArancel($id)
    {
        $this->custom_authorize('read_grados');

        $userDojoId = auth()->user()->dojo_id;

        $arancel = Arancele::query()
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->whereNull('deleted_at')
            ->findOrFail($id);

        $gradoId = $arancel->grado_id;
        $arancel->delete();

        return redirect()->route('voyager.grados.show', ['id' => $gradoId])
            ->with(['message' => 'Arancel eliminado correctamente.', 'alert-type' => 'success']);
    }

    public function updateArancelPrecio(Request $request, $id)
    {
        $this->custom_authorize('read_grados');

        $request->validate([
            'precio' => 'required|numeric|min:0|max:99999999.99',
        ]);

        $userDojoId = auth()->user()->dojo_id;

        $arancel = Arancele::query()
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->whereNull('deleted_at')
            ->findOrFail($id);

        $arancel->precio = $request->precio;
        $arancel->save();

        return redirect()->route('voyager.grados.show', ['id' => $arancel->grado_id])
            ->with(['message' => 'Precio actualizado correctamente.', 'alert-type' => 'success']);
    }
}
