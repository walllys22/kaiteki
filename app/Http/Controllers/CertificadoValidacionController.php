<?php

namespace App\Http\Controllers;

use App\Models\AlumnoGrado;
use App\Models\AlumnoGradoExamen;
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
    public function examen(int $id)
    {
        $examen = AlumnoGradoExamen::with([
            'alumnoGrado.grado',
            'alumnoGrado.alumno.person',
            'alumnoGrado.alumno.dojo.person',
        ])
            ->whereNull('deleted_at')
            ->where('aprobado', 1)
            ->findOrFail($id);

        $alumnoGrado = $examen->alumnoGrado;

        return view('certificados.validar', [
            'tipo' => 'examen',
            'alumnoGrado' => $alumnoGrado,
            'fecha' => $examen->fecha,
            'regId' => $alumnoGrado->id,
        ]);
    }

    /** Certificado de grado en curso. */
    public function cursando(int $id)
    {
        $alumnoGrado = AlumnoGrado::with([
            'grado',
            'alumno.person',
            'alumno.dojo.person',
        ])
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '0');
            })
            ->findOrFail($id);

        return view('certificados.validar', [
            'tipo' => 'cursando',
            'alumnoGrado' => $alumnoGrado,
            'fecha' => $alumnoGrado->fecha,
            'regId' => $alumnoGrado->id,
        ]);
    }
}
