<?php

namespace App\Http\Controllers;


use App\Models\Alumno;
use App\Models\AlumnoTutor;
use App\Models\AlumnoEnfermedade;
use App\Models\Person;
use App\Models\Dojo;
use App\Models\Parentesco;
use Illuminate\Http\Request;
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

        return [
            'dojo' => $dojo,
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
        $userDojoId = auth()->user()->dojo_id;

        $data = Alumno::query()
            ->with(['person', 'dojo'])
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
            ->orderBy('id', 'DESC')
            ->paginate($paginate);

        return view('alumnos.list', compact('data'));
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
            'dojo_id' => 'nullable|exists:dojos,id',
            'person_id' => 'required|exists:people,id',
            'fechaIngreso' => 'required|date',
            'status' => 'required|integer',
            'observacion' => 'nullable|string|max:255',
        ]);

        try {
            $this->validateRelationsForDojo((int) $dojoId, $request);

            Alumno::create([
                'dojo_id' => $dojoId,
                'person_id' => $request->person_id,
                'fechaIngreso' => $request->fechaIngreso,
                'status' => $request->status,
                'observacion' => $request->observacion,
            ]);

            return redirect()->route('voyager.alumnos.index')
                ->with(['message' => 'Alumno creado exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
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
        $people = Person::whereNull('deleted_at')->get();
        $dojo = Dojo::whereNull('deleted_at')->get();
        $parientes = Parentesco::whereNull('deleted_at')->get();
        $enfermedades = AlumnoEnfermedade::whereNull('deleted_at')->get();

        return view('alumnos.read', compact('dojo', 'people', 'enfermedades', 'parientes', 'dataTypeContent'));
    }

    public function tutorList($alumno_id)
    {
        $search = request('search');
        $paginate = request('paginate') ?? 10;

        $data = alumnotutor::with(['tutor', 'pariente'])
            ->where('alumno_id', $alumno_id)
            ->when($search, function ($query, $search) {
                return $query->whereHas('person', function($q) use ($search) {
                    $q->where('first_name', 'like', "%$search%");
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate($paginate);   

        return view('alumnos.Parentesco.list', compact('data', 'alumno_id'));
    }

    public function storeAlumnoTutor(Request $request)
    {
     //   $this->custom_authorize('add_alumnos');

        try {
            $data = $request->all();

            alumnotutor::create($data);

            return redirect()->route('voyager.alumnos.show', ['id' => $request->alumno_id])
                ->with(['message' => 'Alumno creado exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function storeAlumnoEnfermedad(Request $request)
    {

        try {
            $data = $request->all();

            AlumnoEnfermedade::create($data);

            return redirect()->route('voyager.alumnos.show', ['id' => $request->alumno_id])
                ->with(['message' => 'Alumno creado exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }
    

    public function tutorDestroy($id)
    {
        $alumnoTutor = alumnotutor::findOrFail($id);
   
        try {
            
            $alumnoTutor->delete();

            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoTutor->alumno_id])
                ->with(['message' => 'Tutor eliminado del alumno.', 'alert-type' => 'success']);
            
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function enfermedadList($alumno_id)
    {
        $search = request('search');
        $paginate = request('paginate') ?? 10;

        $data = AlumnoEnfermedade::with(['alumno'])
            ->where('alumno_id', $alumno_id)
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('enfermedad', 'like', "%$search%")
                      ->orWhere('medicamento', 'like', "%$search%");
                });
            })
            ->orderBy('id', 'DESC')
            ->paginate($paginate);   

        return view('alumnos.enfermedad.list', compact('data', 'alumno_id'));
    }

    public function enfermedadDestroy($id)
    {
        $alumnoEnfer = AlumnoEnfermedade::findOrFail($id);
   
        try {
            
            $alumnoEnfer->delete();

            return redirect()->route('voyager.alumnos.show', ['id' => $alumnoEnfer->alumno_id])
                ->with(['message' => 'Tutor eliminado del alumno.', 'alert-type' => 'success']);
            
        } catch (\Throwable $th) {
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
    }



}
