<?php

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PersonController extends Controller
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

    public function index()
    {
        $this->custom_authorize('browse_people');

        return view('administrations.people.browse');
    }

    public function list()
    {
        $search = request('search') ?? null;
        $paginate = request('paginate') ?? 10;
        $userDojoId = auth()->user()->dojo_id;

        $data = Person::query()
            ->with('dojo')
            ->when($search, function ($query, $search) {
                return $query->where(function ($q) use ($search) {
                    $q->where('id', $search)
                        ->orWhere('ci', 'like', "%$search%")
                        ->orWhere('phone', 'like', "%$search%")
                        ->orWhere('first_name', 'like', "%$search%");
                });
            })
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->paginate($paginate);

        return view('administrations.people.list', compact('data'));
    }

    public function store(Request $request)
    {
        $this->custom_authorize('add_people');
        $dojoId = $this->resolveDojoIdFromContext($request);

        $request->validate([
            'documentType' => 'required|string|in:Ci,Nit',
            'dojo_id' => 'nullable|exists:dojos,id',
            'ci' => 'required|string|max:255|unique:people,ci',
            'gender' => 'required|string|in:Masculino,Femenino',
            'first_name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,bmp,webp|max:2048',
        ], [
            'ci.required' => 'El numero de cedula es obligatorio',
            'ci.unique' => 'Esta cedula ya esta registrada',
            'first_name.required' => 'El nombre es obligatorio.',
            'dojo_id.exists' => 'La sucursal seleccionada no es valida.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe tener uno de los siguientes formatos: jpeg, jpg, png, bmp, webp.',
            'image.max' => 'La imagen no puede pesar mas de 2 megabytes (MB).',
        ]);

        DB::beginTransaction();

        try {
            Person::create([
                'documentType' => $request->documentType,
                'dojo_id' => $dojoId,
                'ci' => $request->ci,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'first_name' => $request->first_name,
                'email' => $request->email,
                'country_code' => $request->country_code,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => 1,
                'image' => $this->storageController->store_image($request->image, 'people'),
            ]);

            DB::commit();

            return redirect()->route('voyager.people.index')->with(['message' => 'Registrado exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route('voyager.people.index')->with(['message' => $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function update(Request $request, $id)
    {
        $this->custom_authorize('edit_people');
        $ciValidationRule = 'required|string|max:255|unique:people,ci,' . $id;
        $dojoId = $this->resolveDojoIdFromContext($request);

        $request->validate([
            'documentType' => 'required|string|in:Ci,Nit',
            'dojo_id' => 'nullable|exists:dojos,id',
            'ci' => $ciValidationRule,
            'gender' => 'required|string|in:Masculino,Femenino',
            'first_name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,bmp,webp|max:2048',
        ], [
            'ci.required' => 'El numero de cedula es obligatorio',
            'ci.unique' => 'Esta cedula ya esta registrada',
            'first_name.required' => 'El nombre es obligatorio.',
            'dojo_id.exists' => 'La sucursal seleccionada no es valida.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe tener uno de los siguientes formatos: jpeg, jpg, png, bmp, webp.',
            'image.max' => 'La imagen no puede pesar mas de 2 megabytes (MB).',
        ]);

        DB::beginTransaction();

        try {
            $person = Person::findOrFail($id);
            $person->documentType = $request->documentType;
            $person->dojo_id = $dojoId;
            $person->ci = $request->ci;
            $person->birth_date = $request->birth_date;
            $person->gender = $request->gender;
            $person->first_name = $request->first_name;
            $person->email = $request->email;
            $person->country_code = $request->country_code;
            $person->phone = $request->phone;
            $person->address = $request->address;
            $person->status = $request->status == 'on' ? 1 : 0;

            if ($request->image) {
                $person->image = $this->storageController->store_image($request->image, 'people');
            }

            $person->update();

            DB::commit();

            return redirect()->route('voyager.people.index')->with(['message' => 'Actualizada exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();

            return redirect()->route('voyager.people.index')->with(['message' => $th->getMessage(), 'alert-type' => 'error']);
        }
    }

    public function show($id)
    {
        $this->custom_authorize('read_people');
        $userDojoId = auth()->user()->dojo_id;

        $person = Person::query()
            ->when($userDojoId, function ($query, $userDojoId) {
                return $query->where('dojo_id', $userDojoId);
            })
            ->findOrFail($id);

        return view('administrations.people.read', compact('person'));
    }
}
