<?php

namespace App\Http\Controllers;

use App\Models\Grado;

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
}
