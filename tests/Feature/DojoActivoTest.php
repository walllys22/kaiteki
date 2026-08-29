<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Dojo;
use App\Models\Person;
use App\Models\User;
use Tests\TestCase;

class DojoActivoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Loggin middleware usa LARAVEL_START, que solo define public/index.php
        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }
    }

    private function adminGlobal(): User
    {
        return User::whereNull('dojo_id')->firstOrFail();
    }

    private function operador(): User
    {
        return User::whereNotNull('dojo_id')->firstOrFail();
    }

    public function test_admin_global_recibe_dojo_por_defecto()
    {
        $this->actingAs($this->adminGlobal())->get('/admin/people')->assertOk();

        $this->assertNotNull(session(User::DOJO_ACTIVO_SESSION_KEY));
    }

    public function test_listado_de_personas_solo_trae_el_dojo_activo()
    {
        $dojo = Dojo::whereNull('deleted_at')
            ->whereHas('people')->firstOrFail();

        $res = $this->actingAs($this->adminGlobal())
            ->withSession([User::DOJO_ACTIVO_SESSION_KEY => $dojo->id])
            ->get('/admin/people/ajax/list');

        $res->assertOk();
        $data = $res->original->getData()['data'];

        $this->assertGreaterThan(0, $data->total());
        foreach ($data as $person) {
            $this->assertEquals($dojo->id, $person->dojo_id, 'Se filtro una persona de otra sucursal');
        }
    }

    public function test_listado_de_alumnos_solo_trae_el_dojo_activo()
    {
        $dojoId = Alumno::whereNull('deleted_at')->whereNotNull('dojo_id')->firstOrFail()->dojo_id;

        $res = $this->actingAs($this->adminGlobal())
            ->withSession([User::DOJO_ACTIVO_SESSION_KEY => $dojoId])
            ->get('/admin/alumnos/ajax/list');

        $res->assertOk();
        foreach ($res->original->getData()['data'] as $alumno) {
            $this->assertEquals($dojoId, $alumno->dojo_id, 'Se filtro un alumno de otra sucursal');
        }
    }

    public function test_operador_no_puede_ver_ni_editar_persona_de_otra_sucursal()
    {
        $oper = $this->operador();
        $ajena = Person::whereNull('deleted_at')
            ->where('dojo_id', '!=', $oper->getRawOriginal('dojo_id'))
            ->firstOrFail();

        $this->actingAs($oper)->get("/admin/people/{$ajena->id}")->assertNotFound();
        $this->actingAs($oper)->get("/admin/people/{$ajena->id}/edit")->assertNotFound();
        $this->actingAs($oper)->delete("/admin/people/{$ajena->id}")->assertNotFound();
    }

    public function test_operador_ignora_el_dojo_activo_de_sesion()
    {
        $oper = $this->operador();
        $suyo = (int) $oper->getRawOriginal('dojo_id');

        $this->actingAs($oper)
            ->withSession([User::DOJO_ACTIVO_SESSION_KEY => $suyo === 3 ? 2 : 3])
            ->get('/admin/people/ajax/list')
            ->assertOk();

        $this->assertSame($suyo, (int) $oper->fresh()->dojo_id);
    }

    public function test_operador_no_puede_cambiar_de_contexto()
    {
        $this->actingAs($this->operador())
            ->post('/admin/contexto/dojo', ['dojo_id' => 1])
            ->assertForbidden();
    }
}
