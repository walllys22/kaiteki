<?php

namespace App\Http\Controllers;

use App\Models\Dojo;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DojoController extends Controller
{
    public function show($id)
    {
        $this->custom_authorize('read_dojos');

        $userDojoId = Auth::user()->getRawOriginal('dojo_id');

        if ($userDojoId && (int) $userDojoId !== (int) $id) {
            abort(403);
        }

        $dojo = Dojo::with(['person', 'ciudad'])->whereNull('deleted_at')->findOrFail($id);

        $users = User::with(['person', 'role'])
            ->where('dojo_id', $id)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        return view('dojos.read', compact('dojo', 'users'));
    }
}
