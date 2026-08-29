<?php

namespace App\Http\Controllers;

use App\Models\AlumnoGrado;
use App\Models\AlumnoGradoExamen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
     * El motivo NO se le informa a quien consulta ni por el mensaje ni por el
     * codigo HTTP: siempre 404 y siempre el mismo texto. Distinguir "enlace
     * alterado" de "registro inexistente" le indicaria a quien manipula la
     * direccion por donde seguir probando.
     *
     * El detalle queda solo en el log, para que el dojo pueda diagnosticar.
     */
    private function noValido(string $motivo, int $id)
    {
        Log::info('Certificado rechazado', [
            'motivo' => $motivo,
            'id' => $id,
            'ip' => request()->ip(),
        ]);

        return response()->view('certificados.invalido', [], 404);
    }
}
