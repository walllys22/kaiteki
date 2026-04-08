<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use Illuminate\Http\Request;

class AlumnoController extends Controller
{
        protected $storageController;

    public function __construct()
    {
        $this->middleware('auth');
        $this->storageController = new StorageController();
    }

    public function index()
    {
        $this->custom_authorize('browse_alumnos');
        return view('alumnos.browse');
    }

    public function list(){

        $search = request('search') ?? null;
        $paginate = request('paginate') ?? 10;

        $data = Alumno::query()
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

        return view('alumnos.list', compact('data'));
    }





}