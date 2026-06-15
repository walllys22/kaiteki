<?php

namespace App\Http\Controllers;


use App\Models\Alumno;
use App\Models\AlumnoTutor;
use App\Models\AlumnoEnfermedad;
use App\Models\AlumnoGrado;
use App\Models\AlumnoGradoExamen;
use App\Models\AlumnoHorario;
use App\Models\AlumnoMensualidad;
use App\Models\AlumnoMensualidadPlan;
use App\Models\Arancele;
use App\Models\AsistenciaAlumno;
use App\Models\Person;
use App\Models\Dojo;
use App\Models\Parentesco;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Grado;
use App\Models\Horario;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class AlumnoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    protected function resolveDojoIdFromContext(Request $request)
    {
        $userDojoId = auth()->user()->dojo_id;

        if ($userDojoId) {
            return $userDojoId;
        }

        return $request->dojo_id;
    }

    protected function resolveAlumnoDojoId(Request $request): ?int
    {
        $userDojoId = auth()->user()->dojo_id;
        if ($userDojoId) {
            return (int) $userDojoId;
        }

        if (!$request->person_id) {
            return $request->dojo_id ? (int) $request->dojo_id : null;
        }

        $person = Person::query()
            ->whereNull('deleted_at')
            ->find($request->person_id);

        return $person?->dojo_id ? (int) $person->dojo_id : null;
    }

    protected function buildFormData(?Alumno $alumno = null): array
    {
        $userDojoId = auth()->user()->dojo_id;
        $selectedDojoId = old('dojo_id', $userDojoId ?: ($alumno->dojo_id ?? null));

        $dojo = Dojo::query()
            ->whereNull('deleted_at')
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('id', $userDojoId);
            })
            ->orderBy('nombre')
            ->get();

        $grados = Grado::query()
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->ordenado()
            ->get();

        $horarios = Horario::with('dojo')
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->when($userDojoId, fn($q) => $q->where('dojo_id', $userDojoId))
            ->orderBy('nombre')
            ->get();

        return [
            'dojo' => $dojo,
            'grados' => $grados,
            'horarios' => $horarios,
            'selectedDojoId' => $selectedDojoId,
        ];
    }

    protected function validateRelationsForDojo(int $dojoId, Request $request, ?int $alumnoId = null): void
    {
        $personQuery = Person::query()
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->where('dojo_id', $dojoId)
            ->where('id', $request->person_id);

        if (!$personQuery->exists()) {
            throw ValidationException::withMessages([
                'person_id' => 'La persona seleccionada no pertenece a la sucursal indicada.',
            ]);
        }
    }

    protected function validateUniqueAlumnoPerson(Request $request): void
    {
        $existingAlumno = Alumno::withTrashed()
            ->with('dojo')
            ->where('person_id', $request->person_id)
            ->first();

        if ($existingAlumno) {
            $dojoName = optional($existingAlumno->dojo)->nombre ?: 'Sin dojo';

            throw ValidationException::withMessages([
                'person_id' => "La persona seleccionada ya está registrada como alumno en {$dojoName}.",
            ]);
        }
    }

    protected function finPeriodoMensualidad(AlumnoMensualidad $mensualidad): Carbon
    {
        if ($mensualidad->fecha_fin) {
            return Carbon::parse($mensualidad->fecha_fin)->startOfDay();
        }

        return Carbon::parse($mensualidad->periodo)
            ->startOfDay()
            ->addMonthNoOverflow()
            ->subDay();
    }

    protected function alumnoTieneMensualidadVigenteOEnProceso(Alumno $alumno): bool
    {
        $tienePlanEnProceso = AlumnoMensualidadPlan::query()
            ->where('alumno_id', $alumno->id)
            ->where('status', 1)
            ->whereNull('deleted_at')
            ->exists();

        if ($tienePlanEnProceso) {
            return true;
        }

        $hoy = now()->startOfDay();

        return AlumnoMensualidad::query()
            ->where('alumno_id', $alumno->id)
            ->whereNull('deleted_at')
            ->where(function ($query) {
                $query->whereNull('status')
                    ->orWhere('status', '!=', 'anulado');
            })
            ->get()
            ->contains(function (AlumnoMensualidad $mensualidad) use ($hoy) {
                $inicio = Carbon::parse($mensualidad->periodo)->startOfDay();
                $fin = $this->finPeriodoMensualidad($mensualidad);

                return $hoy->gte($inicio) && $hoy->lte($fin);
            });
    }

    public function index()
    {
        $this->custom_authorize('browse_alumnos');
        $dataTypeContent = new Alumno();
        $formData = $this->buildFormData();

        return view('alumnos.browse', array_merge($formData, compact('dataTypeContent')));
    }

    public function list(){

        $search = request('search') ?? null;
        $paginate = request('paginate') ?? 10;
        $estado = request('estado'); // '', 'repaso' (listos para punta), 'examen' (listos para examen)
        $userDojoId = auth()->user()->dojo_id;

        $query = Alumno::query()
            ->with([
                'person',
                'dojo',
                'register',
                'ultimoGrado.grado',
                'ultimoGrado.repasos',
                'alumnoHorarios' => function ($query) {
                    $query->with('horario')
                        ->orderByDesc('status')
                        ->orderByDesc('created_at');
                },
            ])
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('id', $search)
                      ->orWhereHas('person', function($sq) use ($search) {
                          $sq->where('first_name', 'like', "%$search%");
                      });
                });
            })
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->whereNotNull('person_id')
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC');

        if (in_array($estado, ['repaso', 'examen'], true)) {
            // Filtro por estado de progresión del grado activo (calculado, dojo-scoped).
            // repaso → grado Kyu activo con puntas incompletas (aún puede dar repaso/punta)
            // examen → puntas completas o grado Dan (listo para examen final)
            $filtered = $query->get()->filter(function ($item) use ($estado) {
                $ag = $item->ultimoGrado;
                if (!$ag || (string) $ag->status === '1' || !$ag->grado) {
                    return false;
                }
                $usaRepasos   = $ag->grado->usaRepasos();
                $req          = (int) $ag->grado->puntas;
                $aprob        = $ag->repasos->where('aprobado', 1)->count();
                $cumplePuntas = $usaRepasos ? ($aprob >= $req) : true;

                return $estado === 'repaso'
                    ? ($usaRepasos && !$cumplePuntas)
                    : $cumplePuntas;
            })->values();

            $page  = (int) (request('page') ?? 1);
            $items = $filtered->forPage($page, $paginate)->values();
            $data  = new \Illuminate\Pagination\LengthAwarePaginator(
                $items,
                $filtered->count(),
                $paginate,
                $page,
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            );
        } else {
            $data = $query->paginate($paginate);
        }

        return view('alumnos.list', compact('data', 'estado'));
    }

    public function create()
    {
        $this->custom_authorize('add_alumnos');
        $dataTypeContent = new Alumno(); // Objeto vacío para la vista
        $formData = $this->buildFormData();

        return view('alumnos.edit-add', array_merge($formData, compact('dataTypeContent')));
    }


    public function store(Request $request)
    {
        $this->custom_authorize('add_alumnos');
        $dojoId = $this->resolveAlumnoDojoId($request);
        if (!$dojoId) {
            return redirect()->back()
                ->withInput()
                ->with(['message' => 'Debe seleccionar un dojo para registrar al alumno.', 'alert-type' => 'error']);
        }
        $request->validate([
            'dojo_id'     => 'nullable|exists:dojos,id',
            'person_id'   => 'required|exists:people,id',
            'fechaIngreso'=> 'required|date|before_or_equal:today',
            'grado_id'    => 'required|exists:grados,id',
            'horario_id'  => 'required|exists:horarios,id',
            'status'      => 'required|integer',
            'observacion' => 'nullable|string|max:255',
        ]);

        try {
            $this->validateRelationsForDojo((int) $dojoId, $request);
            $this->validateUniqueAlumnoPerson($request);

            DB::beginTransaction();

            $alumno = Alumno::create([
                'dojo_id' => $dojoId,
                'person_id' => $request->person_id,
                'fechaIngreso' => $request->fechaIngreso,
                'status' => $request->status,
                'observacion' => $request->observacion,
            ]);

            AlumnoGrado::create([
                'alumno_id' => $alumno->id,
                'grado_id'  => $request->grado_id,
                'fecha'     => $request->fechaIngreso,
                'observacion' => 'Registro inicial del alumno',
                'status'    => '0',
            ]);

            if ($request->horario_id) {
                AlumnoHorario::create([
                    'alumno_id'  => $alumno->id,
                    'horario_id' => $request->horario_id,
                    'status'     => '1',
                ]);
            }

            DB::commit();

            return redirect()->route('voyager.alumnos.index')
                ->with(['message' => 'Alumno creado exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollBack();
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    /**
     * Edita la fecha de ingreso del alumno. Solo permitido para usuarios con rol 'admin'.
     */
    public function updateFechaIngreso(Request $request, $id)
    {
        $this->custom_authorize('edit_alumnos');

        if (!in_array(optional(auth()->user()->role)->name, ['admin', 'administrador'], true)) {
            abort(403, 'Solo el administrador puede editar la fecha de ingreso.');
        }

        $request->validate([
            'fechaIngreso' => 'required|date|before_or_equal:today',
        ], [
            'fechaIngreso.before_or_equal' => 'La fecha de ingreso no puede ser mayor a la fecha del sistema.',
        ]);

        $userDojoId = auth()->user()->dojo_id;
        $alumno = Alumno::when($userDojoId, fn($q) => $q->where('dojo_id', $userDojoId))
            ->findOrFail($id);

        $alumno->fechaIngreso = $request->fechaIngreso;
        $alumno->save();

        return redirect()->route('voyager.alumnos.show', ['id' => $alumno->id])
            ->with(['message' => 'Fecha de ingreso actualizada exitosamente.', 'alert-type' => 'success']);
    }

    public function show($id)
    {
        $this->custom_authorize('read_alumnos');
        $userDojoId = auth()->user()->dojo_id;
        $dataTypeContent = Alumno::with(['person', 'dojo'])
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->findOrFail($id);
        $people = Person::whereNull('deleted_at')
            ->where('dojo_id', $dataTypeContent->dojo_id)
            ->where('status', 1)
            ->orderBy('first_name', 'ASC')
            ->get();
        $dojo = Dojo::whereNull('deleted_at')->orderBy('nombre', 'ASC')->get();

        $parientes = Parentesco::whereNull('deleted_at')->orderBy('nombre', 'ASC')->get();
        $enfermedades = AlumnoEnfermedad::whereNull('deleted_at')->get();

        // Excluir grados ya registrados y los de orden inferior al máximo alcanzado
        $gradosUsados = AlumnoGrado::where('alumno_id', $id)
            ->whereNotNull('grado_id')
            ->whereNull('deleted_at')
            ->pluck('grado_id')
            ->toArray();

        $maxOrden = DB::table('alumno_grados')
            ->join('grados', 'alumno_grados.grado_id', '=', 'grados.id')
            ->where('alumno_grados.alumno_id', $id)
            ->whereNull('alumno_grados.deleted_at')
            ->whereNotNull('grados.orden')
            ->max('grados.orden');

        $grados = Grado::whereNull('deleted_at')
            ->where('status', 1)
            ->whereNotIn('id', $gradosUsados)
            ->when($maxOrden, fn($q) => $q->where(fn($inner) =>
                $inner->whereNull('orden')->orWhere('orden', '>', $maxOrden)
            ))
            ->ordenado()
            ->get();

        $horarios = Horario::whereNull('deleted_at')
            ->where('status', 1)
            ->when($userDojoId, fn($q) => $q->where('dojo_id', $userDojoId))
            ->orderBy('nombre')
            ->get();

        return view('alumnos.read', compact('dojo', 'people', 'enfermedades', 'parientes', 'grados', 'horarios', 'dataTypeContent'));
    }

    public function kardex($id)
    {
        $this->custom_authorize('read_alumnos');
        $userDojoId = auth()->user()->dojo_id;

        $alumno = Alumno::with(['person', 'dojo'])
            ->when($userDojoId, fn($q) => $q->where('dojo_id', $userDojoId))
            ->findOrFail($id);

        $tutores = AlumnoTutor::with(['tutor', 'pariente'])
            ->where('alumno_id', $id)
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->get();

        $enfermedades = AlumnoEnfermedad::where('alumno_id', $id)
            ->whereNull('deleted_at')
            ->where('status', 1)
            ->get();

        return view('alumnos.partials.kardex', compact('alumno', 'tutores', 'enfermedades'));
    }

    public function historialGrados($id)
    {
        $this->custom_authorize('read_alumnos');
        $userDojoId = auth()->user()->dojo_id;

        $alumno = Alumno::with(['person', 'dojo'])
            ->when($userDojoId, fn($q) => $q->where('dojo_id', $userDojoId))
            ->findOrFail($id);

        $grados = AlumnoGrado::with(['grado', 'repasos', 'examenes'])
            ->where('alumno_id', $id)
            ->whereNull('deleted_at')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get();

        return view('alumnos.partials.historial-grados', compact('alumno', 'grados'));
    }

    public function updateStatus($id)
    {
        $this->custom_authorize('edit_alumnos');

        $userDojoId = auth()->user()->dojo_id;
        $alumno = Alumno::when($userDojoId, function ($query, $userDojoId) {
            return $query->where('dojo_id', $userDojoId);
        })->findOrFail($id);

        if ((int) $alumno->status === 1 && $this->alumnoTieneMensualidadVigenteOEnProceso($alumno)) {
            return back()->with([
                'message' => 'No se puede inactivar al alumno porque tiene una mensualidad vigente o en proceso. Primero debe finalizar o pausar su mensualidad.',
                'alert-type' => 'error',
            ]);
        }

        $alumno->status = $alumno->status == 1 ? 0 : 1;
        $alumno->save();

        $msg = $alumno->status == 1 ? 'Activo' : 'Inactivo';

        return back()->with(['message' => "El estado del alumno se cambió a $msg.", 'alert-type' => 'success']);
    }

    public function checkHistorial($alumno_id)
    {
        $this->custom_authorize('read_alumnos');

        $hasHistorial =
            AlumnoGrado::where('alumno_id', $alumno_id)->exists() ||
            AlumnoTutor::where('alumno_id', $alumno_id)->exists() ||
            AlumnoEnfermedad::where('alumno_id', $alumno_id)->exists();

        return response()->json(['has_historial' => $hasHistorial]);
    }

    public function checkRegistration(Request $request, $person_id)
    {
        $dojo_id = $request->dojo_id;

        $alumnoRegistrado = Alumno::withTrashed()
            ->with('dojo')
            ->where('person_id', $person_id)
            ->first();

        if ($alumnoRegistrado) {
            return response()->json(['status' => 'exists', 'dojo' => optional($alumnoRegistrado->dojo)->nombre ?? 'N/A']);
        }

        $dojoResponsable = Dojo::where('person_id', $person_id)
            ->where('id', $dojo_id)
            ->first();

        if ($dojoResponsable) {
            return response()->json(['status' => 'responsible_same_dojo', 'dojo' => $dojoResponsable->nombre ?? 'N/A']);
        }

        return response()->json(['status' => 'ok']);
    }

    public function print(Request $request)
    {
        $this->custom_authorize('read_alumnos');

        $userDojoId = auth()->user()->dojo_id;
        $dojo_id    = $userDojoId ?: $request->input('dojo_id');
        $grado_id   = $request->input('grado_id');

        $alumnos = Alumno::with(['person', 'dojo', 'ultimoGrado.grado', 'ultimoGrado.repasos'])
            ->when($dojo_id, fn($q) => $q->where('dojo_id', $dojo_id))
            ->when($grado_id, fn($q) => $q->whereHas('ultimoGrado', fn($inner) => $inner->where('grado_id', $grado_id)))
            ->whereNull('deleted_at')
            ->orderBy('dojo_id')
            ->orderByDesc('status')
            ->get();

        $dojo       = $dojo_id ? Dojo::find($dojo_id) : null;
        $gradoFiltro = $grado_id ? Grado::find($grado_id) : null;
        $esGlobal   = !$userDojoId;

        return view('alumnos.print', compact('alumnos', 'dojo', 'gradoFiltro', 'esGlobal'));
    }

    public function tutorList($alumno_id)
    {
        $search = request('search');
        $paginate = request('paginate') ?? 10;
        $alumnoActivo = (int) optional(Alumno::find($alumno_id))->status === 1;

        $data = AlumnoTutor::with(['tutor', 'pariente'])
            ->where('alumno_id', $alumno_id)
            ->when($search, function ($query, $search) {
                return $query->whereHas('tutor', function($q) use ($search) {
                    $q->where('first_name', 'like', "%$search%");
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate($paginate);   

        return view('alumnos.tutores.list', compact('data', 'alumno_id', 'alumnoActivo'));
    }

    public function storeAlumnoTutor(Request $request)
    {
        $this->custom_authorize('add_alumnos');
        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'person_id' => 'required|exists:people,id',
            'parentesco_id' => 'required|exists:parentescos,id',
            'observacion' => 'nullable|string|max:255',
        ]);

        try {
            $alumno = Alumno::with('person')->findOrFail($request->alumno_id);
            $this->ensureAlumnoActivo($alumno);

            if ((int) $alumno->person_id === (int) $request->person_id) {
                return redirect()->back()
                    ->with(['message' => 'La misma persona registrada como alumno no puede registrarse como tutor.', 'alert-type' => 'error']);
            }

            $existingTutor = AlumnoTutor::where('alumno_id', $request->alumno_id)
                ->where('person_id', $request->person_id)
                ->whereNull('deleted_at')
                ->exists();

            if ($existingTutor) {
                return redirect()->back()
                    ->with(['message' => 'La persona seleccionada ya está registrada como tutor de este alumno.', 'alert-type' => 'error']);
            }

            AlumnoTutor::create([
                'alumno_id' => $request->alumno_id,
                'person_id' => $request->person_id,
                'parentesco_id' => $request->parentesco_id,
                'observacion' => $request->observacion,
                'status' => 1,
            ]);

            return redirect()->route('voyager.alumnos.show', ['id' => $request->alumno_id])
                ->with(['message' => 'Tutor registrado exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function storeAlumnoEnfermedad(Request $request)
    {
        $this->custom_authorize('add_alumnos');
        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
            'nombre' => 'required|string|max:255',
            'medicamento' => 'nullable|string|max:255',
            'administracion' => 'nullable|string|max:255',
            'observacion' => 'nullable|string|max:1000',
        ]);

        try {
            $this->ensureAlumnoActivo((int) $request->alumno_id);

            AlumnoEnfermedad::create([
                'alumno_id' => $request->alumno_id,
                'nombre' => $request->nombre,
                'medicamento' => $request->medicamento,
                'administracion' => $request->administracion,
                'observacion' => $request->observacion,
                'status' => 1,
            ]);

            return redirect()->route('voyager.alumnos.show', ['id' => $request->alumno_id])
                ->with(['message' => 'Enfermedad registrada exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }
    

    public function tutorDestroy($id)
    {
        $alumnoTutor = AlumnoTutor::with('alumno')->findOrFail($id);
        $this->ensureAlumnoActivo($alumnoTutor->alumno);
   
        try {
            
            $alumnoTutor->delete();

            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoTutor->alumno_id])
                ->with(['message' => 'Tutor eliminado del alumno.', 'alert-type' => 'success']);
            
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function tutorUpdateStatus($id)
    {
        $this->custom_authorize('edit_alumnos');

        $alumnoTutor = AlumnoTutor::with('alumno')->findOrFail($id);
        $this->ensureAlumnoActivo($alumnoTutor->alumno);

        try {
            $isActive = (string) $alumnoTutor->status === '1' || $alumnoTutor->status === 1 || $alumnoTutor->status === null;
            $alumnoTutor->status = $isActive ? 0 : 1;
            $alumnoTutor->save();

            $msg = $alumnoTutor->status == 1 ? 'Tutor activado correctamente.' : 'Tutor desactivado correctamente.';

            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoTutor->alumno_id])
                ->with(['message' => $msg, 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function gradoList($alumno_id)
    {
        $search   = request('search');
        $paginate = request('paginate') ?? 10;
        $alumno = Alumno::findOrFail($alumno_id);
        $alumnoActivo = (int) $alumno->status === 1;

        // Grado activo (en progreso): el más reciente con status != '1'
        $activeGrado = AlumnoGrado::with(['alumno', 'grado', 'repasos', 'examenes'])
            ->where('alumno_id', $alumno_id)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '0');
            })
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->first();

        $progress = null;
        if ($activeGrado) {
            $progress = AlumnoGradoController::calcularProgreso($activeGrado);
        }

        $arancelRepaso = null;
        $arancelExamen = null;
        if ($activeGrado && $activeGrado->alumno && $activeGrado->alumno->dojo_id) {
            if ($activeGrado->grado && $activeGrado->grado->usaRepasos()) {
                $arancelRepaso = Arancele::query()
                    ->where('grado_id', $activeGrado->grado_id)
                    ->where('dojo_id', $activeGrado->alumno->dojo_id)
                    ->where('tipo', 'Repaso')
                    ->where('status', 1)
                    ->whereNull('deleted_at')
                    ->first();
            }

            $arancelExamen = Arancele::query()
                ->where('grado_id', $activeGrado->grado_id)
                ->where('dojo_id', $activeGrado->alumno->dojo_id)
                ->where('tipo', 'Examen')
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->first();
        }

        // Grados completados (historial)
        $data = AlumnoGrado::with(['alumno', 'grado', 'repasos', 'examenes'])
            ->where('alumno_id', $alumno_id)
            ->where('status', '1')
            ->when($search, function ($query, $search) {
                return $query->whereHas('grado', function ($q) use ($search) {
                    $q->where('tipo', 'like', "%$search%")
                        ->orWhere('numero', 'like', "%$search%")
                        ->orWhere('nombre', 'like', "%$search%");
                });
            })
            ->whereNull('deleted_at')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate($paginate);

        // Puede agregar nuevo grado si no hay uno en progreso, o si el activo está completo
        $puedeAgregarGrado = !$activeGrado || ($progress && $progress['isComplete']);

        // Primer grado asignado al alumno (para regla de certificados)
        $primerAlumnoGrado = AlumnoGrado::where('alumno_id', $alumno_id)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->first();
        $primerAlumnoGradoId = $primerAlumnoGrado?->id;

        $primerGradoEsPrimeroGlobal = false;
        if ($primerAlumnoGrado?->grado_id) {
            $minOrdenGlobal = Grado::whereNotNull('orden')->whereNull('deleted_at')->min('orden');
            $ordenPrimerGrado = Grado::whereNull('deleted_at')->where('id', $primerAlumnoGrado->grado_id)->value('orden');
            $primerGradoEsPrimeroGlobal = $minOrdenGlobal !== null && $ordenPrimerGrado !== null
                && (int) $ordenPrimerGrado === (int) $minOrdenGlobal;
        }

        // Fecha mínima para el próximo grado: fecha del examen final aprobado del último grado completado
        $minFechaGrado = null;
        $ultimoCompletado = AlumnoGrado::where('alumno_id', $alumno_id)
            ->where('status', '1')
            ->whereNull('deleted_at')
            ->orderByDesc('id')
            ->first();
        if ($ultimoCompletado) {
            $minFechaGrado = AlumnoGradoExamen::where('alumno_grado_id', $ultimoCompletado->id)
                ->where('aprobado', 1)
                ->whereNull('deleted_at')
                ->orderByDesc('fecha')
                ->value('fecha');
        }

        // Grados disponibles: excluir los ya registrados y los de orden inferior al máximo alcanzado
        $gradosUsados = AlumnoGrado::where('alumno_id', $alumno_id)
            ->whereNotNull('grado_id')
            ->whereNull('deleted_at')
            ->pluck('grado_id')
            ->toArray();

        $maxOrden = DB::table('alumno_grados')
            ->join('grados', 'alumno_grados.grado_id', '=', 'grados.id')
            ->where('alumno_grados.alumno_id', $alumno_id)
            ->whereNull('alumno_grados.deleted_at')
            ->whereNotNull('grados.orden')
            ->max('grados.orden');

        $grados = Grado::whereNull('deleted_at')
            ->where('status', 1)
            ->whereNotIn('id', $gradosUsados)
            ->when($maxOrden, fn($q) => $q->where(fn($inner) =>
                $inner->whereNull('orden')->orWhere('orden', '>', $maxOrden)
            ))
            ->ordenado()
            ->get();

        return view('alumnos.grados.list', compact(
            'data', 'alumno_id', 'activeGrado', 'progress', 'puedeAgregarGrado', 'grados', 'minFechaGrado', 'arancelRepaso', 'arancelExamen', 'alumnoActivo',
            'primerAlumnoGradoId', 'primerGradoEsPrimeroGlobal'
        ));
    }

    public function enfermedadList($alumno_id)
    {
        $search = request('search');
        $paginate = request('paginate') ?? 10;
        $alumnoActivo = (int) optional(Alumno::find($alumno_id))->status === 1;

        $data = AlumnoEnfermedad::with(['alumno'])
            ->where('alumno_id', $alumno_id)
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('nombre', 'like', "%$search%")
                      ->orWhere('medicamento', 'like', "%$search%");
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate($paginate);   

        return view('alumnos.enfermedades.list', compact('data', 'alumno_id', 'alumnoActivo'));
    }

    public function enfermedadDestroy($id)
    {
        $alumnoEnfer = AlumnoEnfermedad::with('alumno')->findOrFail($id);
        $this->ensureAlumnoActivo($alumnoEnfer->alumno);

        try {

            $alumnoEnfer->delete();

            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoEnfer->alumno_id])
                ->with(['message' => 'Enfermedad eliminada del alumno.', 'alert-type' => 'success']);

        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function horarioList($alumno_id)
    {
        $this->custom_authorize('read_alumnos');

        $search   = request('search');
        $paginate = request('paginate') ?? 10;
        $userDojoId = auth()->user()->dojo_id;
        $alumnoActivo = (int) optional(Alumno::find($alumno_id))->status === 1;

        $data = AlumnoHorario::with(['horario.dojo'])
            ->where('alumno_id', $alumno_id)
            ->whereNull('deleted_at')
            ->when($search, function ($query, $search) {
                return $query->whereHas('horario', function ($q) use ($search) {
                    $q->where('nombre', 'like', "%$search%")
                      ->orWhere('tipo', 'like', "%$search%");
                });
            })
            ->orderByDesc('id')
            ->paginate($paginate);

        // Horarios disponibles para asignar (activos, del dojo del usuario)
        $horarioIds = AlumnoHorario::where('alumno_id', $alumno_id)
            ->where('status', '1')
            ->whereNull('deleted_at')
            ->pluck('horario_id')
            ->toArray();

        $horarios = Horario::whereNull('deleted_at')
            ->where('status', 1)
            ->when($userDojoId, fn($q) => $q->where('dojo_id', $userDojoId))
            ->whereNotIn('id', $horarioIds)
            ->orderBy('nombre')
            ->get();

        return view('alumnos.horarios.list', compact('data', 'alumno_id', 'horarios', 'alumnoActivo'));
    }

    public function asistenciaList($alumno_id)
    {
        $this->custom_authorize('read_alumnos');

        $search   = request('search');
        $paginate = request('paginate') ?? 10;

        $data = AsistenciaAlumno::with(['asistencia.horario', 'asistencia.dojo'])
            ->where('alumno_id', $alumno_id)
            ->whereHas('asistencia', fn($q) => $q->whereNull('deleted_at'))
            ->when($search, function ($q, $search) {
                $q->whereHas('asistencia', function ($sq) use ($search) {
                    $sq->where('fecha', 'like', "%$search%")
                       ->orWhereHas('horario', fn($hq) =>
                           $hq->where('nombre', 'like', "%$search%")
                              ->orWhere('tipo', 'like', "%$search%")
                       );
                });
            })
            ->orderByDesc(
                \App\Models\Asistencia::select('fecha')
                    ->whereColumn('asistencias.id', 'asistencia_alumnos.asistencia_id')
                    ->limit(1)
            )
            ->paginate($paginate);

        // Contadores totales del alumno
        $totales = AsistenciaAlumno::where('alumno_id', $alumno_id)
            ->whereHas('asistencia', fn($q) => $q->whereNull('deleted_at'))
            ->selectRaw("
                COUNT(*) as total,
                SUM(estado = 'asistencia') as presentes,
                SUM(estado = 'licencia') as licencias,
                SUM(estado = 'falta') as faltas
            ")
            ->first();

        return view('alumnos.asistencias.list', compact('data', 'alumno_id', 'totales'));
    }


}
