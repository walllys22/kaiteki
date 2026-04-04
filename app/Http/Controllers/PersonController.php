<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PersonController extends Controller
{
    protected $storageController;
    public function __construct()
    {
        $this->middleware('auth');
        $this->storageController = new StorageController();
    }

    public function index()
    {
        $this->custom_authorize('browse_people');

        return view('administrations.people.browse');
    }
    
    public function list(){

        $search = request('search') ?? null;
        $paginate = request('paginate') ?? 10;

        $data = Person::query()
            // El método when() hace lo mismo que tu "if($search)"
            ->when($search, function ($query, $search) {
                return $query->where(function($q) use ($search) {
                    $q->where('id', $search)
                      ->orWhere('ci', 'like', "%$search%")
                      ->orWhere('phone', 'like', "%$search%")
                      ->orWhere(DB::raw("CONCAT(first_name, ' ', COALESCE(paternal_surname, ''), ' ', COALESCE(maternal_surname, ''))"), 'like', "%$search%")
                      ->orWhere(DB::raw("CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', COALESCE(paternal_surname, ''), ' ', COALESCE(maternal_surname, ''))"), 'like', "%$search%");
                });
            })
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->paginate($paginate);

        return view('administrations.people.list', compact('data'));
    }

    public function store(Request $request)
    {
        $this->custom_authorize('add_people');
        $request->validate([
            'documentType' => 'required|string|in:Ci,Nit',
            'ci' => 'required|string|max:255|unique:people,ci', // Agregar unique aquí
            // 'birth_date' => 'required|date',
            'gender' => 'required|string|in:Masculino,Femenino',
            'first_name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,bmp,webp|max:2048' // 🎉 CAMBIO AQUÍ: Se añade max:3072
        ],
        [
            'ci.required' => 'El número de cédula es obligatorio',
            'ci.unique' => 'Esta cédula ya está registrada',
            // 'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
            'first_name.required' => 'El nombre es obligatorio.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe tener uno de los siguientes formatos: jpeg, jpg, png, bmp, webp.',
            'image.max' => 'La imagen no puede pesar más de 2 megabytes (MB).' // ✍️ CAMBIO AQUÍ: Mensaje personalizado para el tamaño
        ]);
        try {
            // Si envian las imágenes
            Person::create([
                'documentType' => $request->documentType,
                'ci' => $request->ci,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'paternal_surname' => $request->paternal_surname,
                'maternal_surname' => $request->maternal_surname,
                'email' => $request->email,
                'country_code'=> $request->country_code,
                'phone' => $request->phone,
                'address' => $request->address,
                'image' => $this->storageController->store_image($request->image, 'people')
            ]);

            DB::commit();
            return redirect()->route('voyager.people.index')->with(['message' => 'Registrado exitosamente', 'alert-type' => 'success']);
        } catch (\Throwable $th) {
            DB::rollback();
            return redirect()->route('voyager.people.index')->with(['message' => $th->getMessage(), 'alert-type' => 'error']);
        }
    }


    public function update(Request $request, $id){
        $this->custom_authorize('edit_people');
        $ci_validation_rule = 'required|string|max:255|unique:people,ci,' . $id;

        $request->validate([
            // Use the new variable here
            'documentType' => 'required|string|in:Ci,Nit',
            'ci' => $ci_validation_rule, 
            
            // 'birth_date' => 'required|date',
            'gender' => 'required|string|in:Masculino,Femenino',
            'first_name' => 'required|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,bmp,webp|max:2048' 
        ],

        [
            'ci.required' => 'El número de cédula es obligatorio',
            'ci.unique' => 'Esta cédula ya está registrada',
            // 'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
            'first_name.required' => 'El nombre es obligatorio.',
            'image.image' => 'El archivo debe ser una imagen.',
            'image.mimes' => 'La imagen debe tener uno de los siguientes formatos: jpeg, jpg, png, bmp, webp.',
            'image.max' => 'La imagen no puede pesar más de 2 megabytes (MB).' 
        ]);

        DB::beginTransaction();
        try {
            
            $person = Person::find($id);
            $person->documentType = $request->documentType;
            $person->ci = $request->ci;
            $person->birth_date = $request->birth_date;
            $person->gender = $request->gender;
            $person->first_name = $request->first_name;
            $person->middle_name = $request->middle_name;
            $person->paternal_surname = $request->paternal_surname;
            $person->maternal_surname = $request->maternal_surname;
            $person->email = $request->email;
            $person->country_code = $request->country_code;
            $person->phone = $request->phone;
            $person->address = $request->address;
            $person->status = $request->status=='on' ? 1 : 0;

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
        $person = Person::findOrFail($id);
        return view('administrations.people.read', compact('person'));
    }
}
