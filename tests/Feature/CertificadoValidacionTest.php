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
        $res->assertSee('CERTIFICADO VALIDO');
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

    public function test_certificado_de_grado_en_curso()
    {
        $enCurso = AlumnoGrado::whereNull('deleted_at')
            ->where(fn ($q) => $q->whereNull('status')->orWhere('status', '0'))
            ->firstOrFail();

        $this->get(CertificadoValidacionController::urlCursando($enCurso->id))
            ->assertOk()
            ->assertSee('GRADO EN CURSO');
    }
}
