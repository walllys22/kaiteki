<?php

namespace App\Http\Controllers;


use App\Models\Alumno;
use App\Models\AlumnoTutor;
use App\Models\AlumnoEnfermedade;
use App\Models\Person;
use App\Models\Dojo;
use App\Models\Grado;
use App\Models\Horario;
use App\Models\Parentesco;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class AlumnoController extends Controller
{
    protected $storageController;

    public function __construct()
    {
        $this->middleware('auth');
        $this->storageController = new StorageController();
    }

    protected function resolveDojoIdFromContext(Request $request)
    {
        $userDojoId = auth()->user()->dojo_id;

        if ($userDojoId) {
            return $userDojoId;
        }

        return $request->dojo_id;
    }

    protected function bloodTypes(): array
    {
        return ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    }

    public function index()
    {
        $this->custom_authorize('browse_alumnos');
        return view('alumnos.browse');
    }

    public function list(){

        $search = request('search') ?? null;
        $paginate = request('paginate') ?? 10;
        $userDojoId = auth()->user()->dojo_id;

        $data = Alumno::query()
            ->with(['person', 'horario', 'grado'])
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
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->paginate($paginate);

        return view('alumnos.list', compact('data'));
    }

    public function create()
    {
        $this->custom_authorize('add_alumnos');
        $userDojoId = auth()->user()->dojo_id;
        $people = Person::whereNull('deleted_at')
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->get();
        $horario = Horario::whereNull('deleted_at')->get();
        $grado = Grado::whereNull('deleted_at')->get();
        $dojo = Dojo::whereNull('deleted_at')
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('id', $userDojoId);
            })
            ->get();
        $dataTypeContent = new Alumno(); // Objeto vacío para la vista
        $bloodTypes = $this->bloodTypes();

        return view('alumnos.edit-add', compact('dojo', 'people', 'grado', 'horario', 'dataTypeContent', 'bloodTypes'));
    }


    public function store(Request $request)
    {
        $this->custom_authorize('add_alumnos');
        $dojoId = $this->resolveDojoIdFromContext($request);
        if (!$dojoId) {
            return redirect()->back()
                ->withInput()
                ->with(['message' => 'Debe seleccionar un dojo para registrar al alumno.', 'alert-type' => 'error']);
        }
        $request->validate([
            'dojo_id' => 'nullable|exists:dojos,id',
            'person_id' => 'required|exists:people,id',
            'entry_date' => 'required|date',
            'horario_id' => 'required|exists:horarios,id',
            'grado_id' => 'required|exists:grados,id',
            'tipoSangre' => 'nullable|in:' . implode(',', $this->bloodTypes()),
            'status' => 'required|integer',
            'observacion' => 'nullable|string|max:255',
        ]);

        try {
            Alumno::create([
                'dojo_id' => $dojoId,
                'person_id' => $request->person_id,
                'entry_date' => $request->entry_date,
                'horario_id' => $request->horario_id,
                'grado_id' => $request->grado_id,
                'tipoSangre' => $request->tipoSangre,
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

    public function edit($id)
    {
        $this->custom_authorize('edit_alumnos');
        $userDojoId = auth()->user()->dojo_id;
        $dataTypeContent = Alumno::when($userDojoId, function ($query, $userDojoId) {
            return $query->where('dojo_id', $userDojoId);
        })->findOrFail($id);
        $people = Person::whereNull('deleted_at')
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->get();
        $horario = Horario::whereNull('deleted_at')->get();
        $grado = Grado::whereNull('deleted_at')->get();
        $dojo = Dojo::whereNull('deleted_at')
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('id', $userDojoId);
            })
            ->get();
        $bloodTypes = $this->bloodTypes();

        return view('alumnos.edit-add', compact('dojo', 'people', 'grado', 'horario', 'dataTypeContent', 'bloodTypes'));
        
    }

    public function show($id)
    {
        $this->custom_authorize('read_alumnos');
        $userDojoId = auth()->user()->dojo_id;
        $dataTypeContent = Alumno::with(['person', 'horario', 'grado'])
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->findOrFail($id);
        $people = Person::whereNull('deleted_at')->get();
        $horario = Horario::whereNull('deleted_at')->get();
        $grado = Grado::whereNull('deleted_at')->get();
        $dojo = Dojo::whereNull('deleted_at')->get();
        $parientes = Parentesco::whereNull('deleted_at')->get();
        $enfermedades = AlumnoEnfermedade::whereNull('deleted_at')->get();

        return view('alumnos.read', compact('dojo', 'people', 'enfermedades', 'grado', 'horario', 'parientes', 'dataTypeContent'));
    }

    public function update(Request $request, $id)
    {
        $this->custom_authorize('edit_alumnos');
        $dojoId = $this->resolveDojoIdFromContext($request);
        if (!$dojoId) {
            return redirect()->back()
                ->withInput()
                ->with(['message' => 'Debe seleccionar un dojo valido para actualizar al alumno.', 'alert-type' => 'error']);
        }

        // Manejo de status (si viene de un checkbox envía "on")
        if ($request->has('status')) {
            $request->merge(['status' => $request->status == 'on' ? 1 : $request->status]);
        }


        $request->validate([
            'dojo_id' => 'nullable|exists:dojos,id',
            'person_id' => 'required',
            'entry_date' => 'required|date',
            'horario_id' => 'required',
            'grado_id' => 'required',
            'tipoSangre' => 'nullable|in:' . implode(',', $this->bloodTypes()),
            'status' => 'nullable', // Permitimos nullable para manejar el checkbox desmarcado
            'observacion' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $alumno = Alumno::when(auth()->user()->dojo_id, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })->findOrFail($id);
            
            // Asignación manual de atributos para evitar problemas de mass assignment ($fillable)
            $alumno->dojo_id = $dojoId;
            $alumno->person_id = $request->person_id;
            $alumno->entry_date = $request->entry_date;
            $alumno->horario_id = $request->horario_id;
            $alumno->grado_id = $request->grado_id;
            $alumno->tipoSangre = $request->tipoSangre;
            $alumno->status = $request->status ? 1 : 0;
            $alumno->observacion = $request->observacion;

            $alumno->update();
            DB::commit();

            return redirect()->route('voyager.alumnos.index')
                ->with(['message' => 'Alumno actualizado exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->back()
                ->with(['message' => 'Error: ' . $th->getMessage(), 'alert-type' => 'error']);
        }
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
