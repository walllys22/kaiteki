<?php

namespace App\Http\Controllers;

use App\Models\AlumnoGrado;
use App\Models\AlumnoGradoExamen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Validacion publica de certificados.
 *
 * El QR impreso en el certificado apunta a una URL firmada de este controlador.
 * No requiere autenticacion: cualquiera que escanee el papel puede comprobar que
 * el certificado es autentico. La firma evita que se pueda enumerar alumnos
 * cambiando el id a mano en la barra de direcciones.
 */
class CertificadoValidacionController extends Controller
{
    /** URL firmada, sin vencimiento: un certificado impreso es permanente. */
    public static function urlExamen(int $examenId): string
    {
        return URL::signedRoute('certificados.validar.examen', ['id' => $examenId]);
    }

    public static function urlCursando(int $alumnoGradoId): string
    {
        return URL::signedRoute('certificados.validar.cursando', ['id' => $alumnoGradoId]);
    }

    /** Certificado de examen aprobado. */
    public function examen(Request $request, int $id)
    {
        if (! $request->hasValidSignature()) {
            return $this->noValido('enlace', $id);
        }

        $examen = AlumnoGradoExamen::with([
            'alumnoGrado.grado',
            'alumnoGrado.alumno.person',
            'alumnoGrado.alumno.dojo.person',
        ])
            ->whereNull('deleted_at')
            ->where('aprobado', 1)
            ->find($id);

        if (! $examen || ! $examen->alumnoGrado) {
            return $this->noValido('inexistente', $id);
        }

        $alumnoGrado = $examen->alumnoGrado;

        return view('certificados.validar', [
            'tipo' => 'examen',
            'alumnoGrado' => $alumnoGrado,
            'fecha' => $examen->fecha,
            'regId' => $alumnoGrado->id,
        ]);
    }

    /** Certificado de grado en curso. */
    public function cursando(Request $request, int $id)
    {
        if (! $request->hasValidSignature()) {
            return $this->noValido('enlace', $id);
        }

        $alumnoGrado = AlumnoGrado::with([
            'grado',
            'alumno.person',
            'alumno.dojo.person',
        ])
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '0');
            })
            ->find($id);

        if (! $alumnoGrado) {
            return $this->noValido('inexistente', $id);
        }

        return view('certificados.validar', [
            'tipo' => 'cursando',
            'alumnoGrado' => $alumnoGrado,
            'fecha' => $alumnoGrado->fecha,
            'regId' => $alumnoGrado->id,
        ]);
    }

    /**
     * Pagina de rechazo.
     *
     * `enlace`      -> la firma no valida: alguien edito la URL (por ejemplo,
     *                  cambio el numero de examen para ver otro certificado).
     * `inexistente` -> la firma es buena pero el registro no existe, fue dado
     *                  de baja o el examen no esta aprobado.
     *
     * Devuelve 403 / 404 de verdad: la pagina es un rechazo, no un resultado.
     */
    private function noValido(string $motivo, int $id)
    {
        $status = $motivo === 'enlace' ? 403 : 404;

        return response()->view('certificados.invalido', [
            'motivo' => $motivo,
            'referencia' => $id,
        ], $status);
    }
}
