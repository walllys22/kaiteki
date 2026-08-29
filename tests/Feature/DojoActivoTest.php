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

    /** Rol administrador: global, pero trabaja parado sobre una sucursal. */
    private function administrador(): User
    {
        return User::whereNull('dojo_id')
            ->whereHas('role', fn ($q) => $q->where('name', 'administrador'))
            ->firstOrFail();
    }

    /** Rol admin: ve todo el sistema, sin selector ni dojo forzado. */
    private function superAdmin(): User
    {
        return User::whereNull('dojo_id')
            ->whereHas('role', fn ($q) => $q->where('name', 'admin'))
            ->firstOrFail();
    }

    private function operador(): User
    {
        return User::whereNotNull('dojo_id')->firstOrFail();
    }

    public function test_administrador_recibe_dojo_por_defecto()
    {
        $this->actingAs($this->administrador())->get('/admin/people')->assertOk();

        $this->assertNotNull(session(User::DOJO_ACTIVO_SESSION_KEY));
    }

    public function test_super_admin_no_queda_atado_a_ninguna_sucursal()
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get('/admin/people')->assertOk();

        $this->assertNull(session(User::DOJO_ACTIVO_SESSION_KEY), 'Al rol admin no se le debe forzar un dojo');
        $this->assertNull($admin->dojo_id, 'El rol admin debe ver todas las sucursales');
        $this->assertFalse($admin->usaDojoActivo());
    }

    public function test_super_admin_ignora_el_dojo_activo_de_sesion()
    {
        $admin = $this->superAdmin();

        $res = $this->actingAs($admin)
            ->withSession([User::DOJO_ACTIVO_SESSION_KEY => 3])
            ->get('/admin/people/ajax/list');

        $res->assertOk();
        $dojos = $res->original->getData()['data']->pluck('dojo_id')->unique();

        $this->assertGreaterThan(1, $dojos->count(), 'El rol admin debe seguir viendo personas de varias sucursales');
    }

    public function test_super_admin_no_puede_usar_el_selector()
    {
        $this->actingAs($this->superAdmin())
            ->post('/admin/contexto/dojo', ['dojo_id' => 3])
            ->assertForbidden();
    }

    public function test_listado_de_personas_solo_trae_el_dojo_activo()
    {
        $dojo = Dojo::whereNull('deleted_at')
            ->whereHas('people')->firstOrFail();

        $res = $this->actingAs($this->administrador())
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

        $res = $this->actingAs($this->administrador())
            ->withSession([User::DOJO_ACTIVO_SESSION_KEY => $dojoId])
            ->get('/admin/alumnos/ajax/list');

        $res->assertOk();
        foreach ($res->original->getData()['data'] as $alumno) {
            $this->assertEquals($dojoId, $alumno->dojo_id, 'Se filtro un alumno de otra sucursal');
        }
    }

    public function test_administrador_no_puede_mover_una_persona_a_otra_sucursal()
    {
        $admin = $this->administrador();
        $dojoActivo = Dojo::whereNull('deleted_at')->whereHas('people')->firstOrFail();
        $otroDojo = Dojo::whereNull('deleted_at')->where('id', '!=', $dojoActivo->id)->firstOrFail();

        $persona = Person::whereNull('deleted_at')->where('dojo_id', $dojoActivo->id)->firstOrFail();

        // Intenta forzar por request el traslado a otra sucursal.
        $this->actingAs($admin)
            ->withSession([User::DOJO_ACTIVO_SESSION_KEY => $dojoActivo->id])
            ->put("/admin/people/{$persona->id}", [
                'dojo_id' => $otroDojo->id,
                'first_name' => $persona->first_name,
                'gender' => $persona->gender ?: 'Masculino',
                'ci' => $persona->ci,
            ]);

        $this->assertEquals(
            $dojoActivo->id,
            $persona->fresh()->dojo_id,
            'El dojo activo debe ganarle al dojo_id mandado por el formulario'
        );
    }

    public function test_formulario_de_edicion_bloquea_el_select_de_sucursal()
    {
        $admin = $this->administrador();
        $dojo = Dojo::whereNull('deleted_at')->whereHas('people')->firstOrFail();
        $persona = Person::whereNull('deleted_at')->where('dojo_id', $dojo->id)->firstOrFail();

        $html = $this->actingAs($admin)
            ->withSession([User::DOJO_ACTIVO_SESSION_KEY => $dojo->id])
            ->get("/admin/people/{$persona->id}/edit")
            ->assertOk()
            ->getContent();

        // Ojo: el sidebar tiene su propio <select name="dojo_id"> (el switcher),
        // por eso se busca puntualmente el select editable del formulario BREAD.
        $this->assertStringNotContainsString(
            '<select name="dojo_id" class="form-control select2" required>',
            $html,
            'El select de sucursal del formulario debe venir bloqueado, no editable'
        );
        $this->assertStringContainsString('<input type="hidden" name="dojo_id"', $html);
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
