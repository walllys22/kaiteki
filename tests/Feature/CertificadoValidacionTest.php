<?php

namespace Tests\Feature;

use App\Http\Controllers\CertificadoValidacionController;
use App\Models\AlumnoGrado;
use App\Models\AlumnoGradoExamen;
use Tests\TestCase;

class CertificadoValidacionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }
    }

    private function examenAprobado(): AlumnoGradoExamen
    {
        return AlumnoGradoExamen::whereNull('deleted_at')->where('aprobado', 1)->firstOrFail();
    }

    public function test_pagina_publica_muestra_los_datos_del_alumno_sin_login()
    {
        $examen = $this->examenAprobado();
        $alumno = $examen->alumnoGrado->alumno;

        $res = $this->get(CertificadoValidacionController::urlExamen($examen->id));

        $res->assertOk();
        $res->assertSee('Certificado verificado');
        $res->assertSee(mb_strtoupper($alumno->person->first_name, 'UTF-8'), false);
        $res->assertSee($alumno->dojo->nombre, false);
        $this->assertGuest();
    }

    public function test_el_documento_va_enmascarado()
    {
        $examen = $this->examenAprobado();
        $ci = (string) $examen->alumnoGrado->alumno->person->ci;

        $res = $this->get(CertificadoValidacionController::urlExamen($examen->id));

        if ($ci !== '' && mb_strlen($ci) > 3) {
            $res->assertDontSee($ci, false);
            $res->assertSee(mb_substr($ci, -3), false);
        } else {
            $this->markTestSkipped('El alumno de prueba no tiene CI cargado');
        }
    }

    public function test_sin_firma_no_se_puede_enumerar_alumnos()
    {
        $examen = $this->examenAprobado();

        $this->get("/validar/certificado/examen/{$examen->id}")->assertForbidden();
    }

    public function test_firma_de_otro_certificado_no_sirve()
    {
        $examen = $this->examenAprobado();
        $otro = AlumnoGradoExamen::whereNull('deleted_at')
            ->where('aprobado', 1)
            ->where('id', '!=', $examen->id)
            ->first();

        if (! $otro) {
            $this->markTestSkipped('Hace falta mas de un examen aprobado');
        }

        $urlAjena = CertificadoValidacionController::urlExamen($examen->id);
        $firma = parse_url($urlAjena, PHP_URL_QUERY);

        $this->get("/validar/certificado/examen/{$otro->id}?{$firma}")->assertForbidden();
    }

    public function test_examen_aplazado_no_tiene_pagina_de_validacion()
    {
        $aplazado = AlumnoGradoExamen::whereNull('deleted_at')
            ->where(fn ($q) => $q->whereNull('aprobado')->orWhere('aprobado', '!=', 1))
            ->first();

        if (! $aplazado) {
            $this->markTestSkipped('No hay examenes aplazados en la base');
        }

        $this->get(CertificadoValidacionController::urlExamen($aplazado->id))->assertNotFound();
    }

    public function test_la_cinta_dibuja_base_y_franja_por_separado()
    {
        // "Cinturon Blanco Franja Amarilla" debe salir blanca con franja amarilla,
        // no amarilla entera.
        $grado = \App\Models\Grado::where('nombre', 'like', '%Franja%')->first();

        if (! $grado) {
            $this->markTestSkipped('No hay grados con franja en la base');
        }

        $alumnoGrado = AlumnoGrado::where('grado_id', $grado->id)->whereNull('deleted_at')->first();

        if (! $alumnoGrado) {
            $this->markTestSkipped('Ningun alumno tiene ese grado');
        }

        $examen = AlumnoGradoExamen::where('alumno_grado_id', $alumnoGrado->id)
            ->where('aprobado', 1)
            ->whereNull('deleted_at')
            ->first();

        $url = $examen
            ? CertificadoValidacionController::urlExamen($examen->id)
            : CertificadoValidacionController::urlCursando($alumnoGrado->id);

        $html = $this->get($url)->assertOk()->getContent();

        $this->assertStringContainsString('#f2efe6', $html, 'Falta el blanco de la cinta');
        $this->assertStringContainsString('#eab308', $html, 'Falta la franja amarilla');
    }

    public function test_la_pagina_es_usable_sin_javascript()
    {
        // El revelado por scroll solo aplica bajo html.js. Si el JS no corre,
        // ningun bloque puede quedar oculto.
        $examen = $this->examenAprobado();

        $html = $this->get(CertificadoValidacionController::urlExamen($examen->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('html.js .revelable', $html, 'El ocultamiento debe estar condicionado a html.js');

        // Ninguna regla puede empezar con .revelable sin el prefijo html.js:
        // eso ocultaria el bloque para siempre si el JS no corre.
        $this->assertSame(
            0,
            preg_match('/^\s*\.revelable[^{]*\{/m', $html),
            'Hay una regla .revelable sin calificar con html.js'
        );

        // Y el movimiento reducido tiene que apagar los efectos.
        $this->assertStringContainsString('prefers-reduced-motion', $html);
    }

    public function test_certificado_de_grado_en_curso()
    {
        $enCurso = AlumnoGrado::whereNull('deleted_at')
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '0'))
            ->firstOrFail();

        $this->get(CertificadoValidacionController::urlCursando($enCurso->id))
            ->assertOk()
            ->assertSee('Grado en curso');
    }
}
