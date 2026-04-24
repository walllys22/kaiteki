<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\HorarioReponsable;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HorarioController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $this->custom_authorize('browse_horarios');

        return view('horarios.browse');
    }

    public function list()
    {
        $search = request('search') ?? null;
        $paginate = request('paginate') ?? 10;
        $userDojoId = auth()->user()->dojo_id;

        $data = Horario::query()
            ->with([
                'dojo',
                'activeResponsible.person',
                'responsibles.person',
            ])
            ->withCount('responsibles')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('id', $search)
                        ->orWhere('tipo', 'like', "%$search%")
                        ->orWhere('nombre', 'like', "%$search%")
                        ->orWhereHas('dojo', function ($dojoQuery) use ($search) {
                            $dojoQuery->where('nombre', 'like', "%$search%");
                        });
                });
            })
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->paginate($paginate);

        return view('horarios.list', compact('data'));
    }

    public function show($id)
    {
        $this->custom_authorize('read_horarios');

        $userDojoId = auth()->user()->dojo_id;

        $horario = Horario::query()
            ->with([
                'dojo',
                'activeResponsible.person',
                'responsibles.person',
            ])
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->findOrFail($id);

        return view('horarios.read', compact('horario'));
    }

    public function storeResponsible(Request $request)
    {
        $this->custom_authorize('edit_horarios');

        $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'person_id' => 'required|exists:people,id',
            'observacion' => 'nullable|string|max:1000',
        ], [
            'person_id.required' => 'Debe seleccionar un responsable.',
        ]);

        $userDojoId = auth()->user()->dojo_id;
        $horario = Horario::query()
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->findOrFail($request->horario_id);

        $person = Person::query()
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->when($horario->dojo_id, function ($query, $dojoId) {
                return $query->where('dojo_id', $dojoId);
            })
            ->findOrFail($request->person_id);

        $activeResponsible = HorarioReponsable::query()
            ->where('horario_id', $horario->id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->first();

        if ($activeResponsible && (int) $activeResponsible->person_id === (int) $person->id) {
            return response()->json([
                'error' => $person->first_name . ' ya es el responsable activo de este horario.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            HorarioReponsable::query()
                ->where('horario_id', $horario->id)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->update([
                    'status' => 0,
                    'updated_at' => now(),
                ]);

            HorarioReponsable::create([
                'horario_id' => $horario->id,
                'person_id' => $person->id,
                'observacion' => $request->observacion,
                'status' => 1,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Responsable asignado correctamente.',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json([
                'error' => 'Ocurrió un error al registrar el responsable.',
            ], 500);
        }
    }
}
