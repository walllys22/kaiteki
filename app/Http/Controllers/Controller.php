<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use DateTime;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    public function custom_authorize($permission){
        if(!Auth::user()->hasPermission($permission)){
            abort(403, 'THIS ACTIO UNAUTHORIZED.');
        }
    }

    protected function ensureAlumnoActivo(Alumno|int $alumno, ?string $message = null): Alumno
    {
        $userDojoId = Auth::user()?->dojo_id;

        if (!$alumno instanceof Alumno) {
            $alumno = Alumno::query()
                ->when($userDojoId, fn($query) => $query->where('dojo_id', $userDojoId))
                ->whereNull('deleted_at')
                ->findOrFail($alumno);
        } elseif ($userDojoId && (int) $alumno->dojo_id !== (int) $userDojoId) {
            abort(404);
        }

        if ((int) $alumno->status !== 1) {
            throw ValidationException::withMessages([
                'alumno_id' => $message ?: 'El alumno esta inactivo. Solo se permite visualizar su informacion.',
            ]);
        }

        return $alumno;
    }
}
