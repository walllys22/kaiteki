<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Person;
use Illuminate\Support\Facades\DB;

class AjaxController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function personList(){
        $q = request('q');
        $data = Person::query()
                        ->when($q, function ($query, $q) {
                            $query->where(function ($subQ) use ($q) {
                                $subQ->where('ci', 'like', "%$q%")
                                    ->orWhere('phone', 'like', "%$q%")
                                    ->orWhere('first_name', 'like', "%$q%");
                            });
                        })
                        ->whereNull('deleted_at')
                        ->get();
        return response()->json($data);
    }

    public function personStore(Request $request){
        $request->validate([
            'documentType' => 'required|string|in:Ci,Nit',
            'dojo_id' => 'nullable|exists:dojos,id',
            'ci' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'gender' => 'required|string|in:Masculino,Femenino',
        ]);

        $person = Person::withTrashed()->where('ci', $request->ci)->first();
        if ($person) {
            return response()->json(['error' => 'El CI ya se encuentra registrado a nombre de: ' . $person->first_name]);
        }
        DB::beginTransaction();
        try {
            $person = Person::create($request->all());
            DB::commit();
            return response()->json(['person' => $person]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['error' => 'Ocurrió un error al guardar...']);
        }
    }
}
