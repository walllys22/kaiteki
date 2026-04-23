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
        $data = Person::OrWhereRaw($q ? "ci like '%$q%'" : 1)
                        ->OrWhereRaw($q ? "phone like '%$q%'" : 1)
                        ->OrWhereRaw($q ? "first_name like '%$q%'" : 1)
                        ->OrWhereRaw($q ? "middle_name like '%$q%'" : 1)
                        ->OrWhereRaw($q ? "paternal_surname like '%$q%'" : 1)
                        ->OrWhereRaw($q ? "maternal_surname like '%$q%'" : 1)
                        ->orWhere(function ($subQ) use ($q) {
                            $subQ->whereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(middle_name, '')) like ?", ["%$q%"])
                                ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(paternal_surname, ''), ' ', COALESCE(maternal_surname, '')) like ?", ["%$q%"])
                                ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(middle_name, ''), ' ', COALESCE(paternal_surname, ''), ' ', COALESCE(maternal_surname, '')) like ?", ["%$q%"]);
                        })
                        ->where('deleted_at', null)
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
