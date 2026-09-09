<?php

namespace Tests\Feature;

use App\Models\Alumno;
use App\Models\Dojo;
use App\Models\DojoMensualidad;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Lista de cumpleanos imprimible (AlumnoController@printCumpleanos).
 *
 * Cubre lo que la hace distinta del resto de reportes:
 * - el rango se aplica por dia/mes, no por fecha absoluta
 * - el rango puede cruzar el fin de anio
 * - el dojo es obligatorio: nunca sale una lista de varias sucursales
 */
class CumpleanosTest extends TestCase
{
    // Los casos de operador necesitan la mensualidad SaaS del dojo vigente (si no,
    // CheckDojoMensualidad redirige al 402). Se da de alta y se revierte al final.
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        // Loggin middleware usa LARAVEL_START, que solo define public/index.php
        if (! defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));
        }
    }

    private const RUTA = '/admin/alumnos/imprimir/cumpleanos';

    private function superAdmin(): User
    {
        return User::whereNull('dojo_id')
            ->whereHas('role', fn ($q) => $q->where('name', 'admin'))
            ->firstOrFail();
    }

    private function administrador(): User
    {
        return User::whereNull('dojo_id')
            ->whereHas('role', fn ($q) => $q->where('name', 'administrador'))
            ->firstOrFail();
    }

    /**
     * Operador de la sucursal con mas alumnos, con la mensualidad SaaS del dojo
     * garantizada: si no esta vigente, CheckDojoMensualidad manda al 402 y el
     * test mediria el bloqueo de facturacion en vez del filtro de cumpleanos.
     */
    private function operador(): User
    {
        $dojoId = Alumno::query()
            ->whereNull('deleted_at')
            ->whereNotNull('person_id')
            ->whereIn('dojo_id', User::whereNotNull('dojo_id')->pluck('dojo_id'))
            ->groupBy('dojo_id')
            ->orderByRaw('COUNT(*) DESC')
            ->value('dojo_id');

        $operador = User::whereNotNull('dojo_id')
            ->when($dojoId, fn ($q) => $q->where('dojo_id', $dojoId))
            ->firstOrFail();

        $this->garantizarMensualidadVigente((int) $operador->getRawOriginal('dojo_id'));

        return $operador;
    }

    private function garantizarMensualidadVigente(int $dojoId): void
    {
        $vigente = DojoMensualidad::where('dojo_id', $dojoId)
            ->whereNull('deleted_at')
            ->where('fecha_fin', '>=', now()->toDateString())
            ->exists();

        if ($vigente) {
            return;
        }

        DojoMensualidad::create([
            'dojo_id'      => $dojoId,
            'fecha_inicio' => now()->startOfMonth()->toDateString(),
            'fecha_fin'    => now()->addMonth()->toDateString(),
            'monto'        => 0,
            'monto_pagado' => 0,
            'observacion'  => 'Alta temporal de test (se revierte)',
        ]);
    }

    private function otroDojo(int $dojoId): Dojo
    {
        return Dojo::whereNull('deleted_at')->where('id', '!=', $dojoId)->firstOrFail();
    }

    private function anioCompleto(array $extra = []): array
    {
        return array_merge([
            'desde'  => '2026-01-01',
            'hasta'  => '2026-12-31',
            'estado' => 'todos',
        ], $extra);
    }

    public function test_el_dojo_es_obligatorio_para_el_rol_admin()
    {
        // El rol admin no tiene dojo propio: si no elige uno, no hay lista.
        $this->actingAs($this->superAdmin())
            ->get(self::RUTA . '?' . http_build_query($this->anioCompleto()))
            ->assertStatus(400);
    }

    public function test_el_rol_admin_imprime_la_sucursal_que_elige()
    {
        $dojo = Dojo::whereNull('deleted_at')->firstOrFail();

        $response = $this->actingAs($this->superAdmin())
            ->get(self::RUTA . '?' . http_build_query($this->anioCompleto(['dojo_id' => $dojo->id])));

        $response->assertOk();
        $this->assertSame($dojo->id, $response->viewData('dojo')->id);

        foreach ($response->viewData('filas') as $fila) {
            $this->assertSame($dojo->id, (int) $fila['alumno']->dojo_id);
        }
    }

    public function test_operador_de_sucursal_ignora_el_dojo_id_del_request()
    {
        $operador = $this->operador();
        $suDojoId = (int) $operador->getRawOriginal('dojo_id');
        $ajeno    = $this->otroDojo($suDojoId);

        // Manda a mano la sucursal ajena: el servidor la tiene que descartar.
        $response = $this->actingAs($operador)
            ->get(self::RUTA . '?' . http_build_query($this->anioCompleto(['dojo_id' => $ajeno->id])));

        $response->assertOk();
        $this->assertSame($suDojoId, $response->viewData('dojo')->id);

        foreach ($response->viewData('filas') as $fila) {
            $this->assertSame($suDojoId, (int) $fila['alumno']->dojo_id);
        }
    }

    public function test_administrador_queda_atado_al_dojo_activo()
    {
        $elegido = Dojo::whereNull('deleted_at')->firstOrFail();
        $ajeno   = $this->otroDojo($elegido->id);

        $response = $this->actingAs($this->administrador())
            ->withSession([User::DOJO_ACTIVO_SESSION_KEY => $elegido->id])
            ->get(self::RUTA . '?' . http_build_query($this->anioCompleto(['dojo_id' => $ajeno->id])));

        $response->assertOk();
        $this->assertSame($elegido->id, $response->viewData('dojo')->id);
    }

    public function test_el_rango_filtra_por_dia_y_mes_sin_mirar_el_anio_de_nacimiento()
    {
        $operador = $this->operador();
        $dojoId   = (int) $operador->getRawOriginal('dojo_id');

        $response = $this->actingAs($operador)->get(self::RUTA . '?' . http_build_query([
            'desde'  => '2026-03-01',
            'hasta'  => '2026-03-31',
            'estado' => 'todos',
        ]));

        $response->assertOk();

        $filas = $response->viewData('filas');
        foreach ($filas as $fila) {
            $this->assertSame(3, (int) $fila['nacimiento']->month, 'Solo deben venir nacidos en marzo');
        }

        // Contraste contra la base: mismo conteo que los alumnos del dojo nacidos en marzo.
        $esperado = Alumno::whereNull('deleted_at')
            ->whereNotNull('person_id')
            ->where('dojo_id', $dojoId)
            ->whereHas('person', fn ($q) => $q->whereNull('deleted_at')->whereMonth('birth_date', 3))
            ->count();

        $this->assertSame($esperado, $filas->count());
    }

    public function test_el_rango_puede_cruzar_el_fin_de_anio()
    {
        $operador = $this->operador();
        $dojoId   = (int) $operador->getRawOriginal('dojo_id');

        $response = $this->actingAs($operador)->get(self::RUTA . '?' . http_build_query([
            'desde'  => '2026-12-15',
            'hasta'  => '2027-01-15',
            'estado' => 'todos',
        ]));

        $response->assertOk();
        $this->assertTrue($response->viewData('cruzaAnio'));

        $filas = $response->viewData('filas');

        foreach ($filas as $fila) {
            $mmdd = (int) $fila['nacimiento']->format('md');
            $this->assertTrue(
                $mmdd >= 1215 || $mmdd <= 115,
                "Fecha fuera del rango que cruza el anio: {$fila['nacimiento']->format('d/m')}"
            );
        }

        $esperado = Alumno::whereNull('deleted_at')
            ->whereNotNull('person_id')
            ->where('dojo_id', $dojoId)
            ->whereHas('person', function ($q) {
                $q->whereNull('deleted_at')->whereNotNull('birth_date')->where(function ($w) {
                    $w->whereRaw('CAST(DATE_FORMAT(people.birth_date, "%m%d") AS UNSIGNED) >= 1215')
                      ->orWhereRaw('CAST(DATE_FORMAT(people.birth_date, "%m%d") AS UNSIGNED) <= 115');
                });
            })
            ->count();

        $this->assertSame($esperado, $filas->count());
    }

    public function test_diciembre_va_antes_que_enero_cuando_el_rango_cruza_el_anio()
    {
        $response = $this->actingAs($this->operador())->get(self::RUTA . '?' . http_build_query([
            'desde'  => '2026-12-15',
            'hasta'  => '2027-01-15',
            'estado' => 'todos',
        ]));

        $response->assertOk();

        $ordenes = $response->viewData('filas')->pluck('orden')->all();
        $this->assertSame(
            collect($ordenes)->sort()->values()->all(),
            $ordenes,
            'Las filas deben salir en el orden cronologico del rango'
        );

        // Y el anio del cumpleanos de enero tiene que ser el siguiente al de diciembre.
        foreach ($response->viewData('filas') as $fila) {
            $anioEsperado = (int) $fila['nacimiento']->format('md') >= 1215 ? 2026 : 2027;
            $this->assertSame($anioEsperado, $fila['cumple']->year);
        }
    }

    public function test_por_defecto_solo_lista_alumnos_activos()
    {
        $response = $this->actingAs($this->operador())->get(self::RUTA . '?' . http_build_query([
            'desde' => '2026-01-01',
            'hasta' => '2026-12-31',
        ]));

        $response->assertOk();

        foreach ($response->viewData('filas') as $fila) {
            $this->assertSame(1, (int) $fila['alumno']->status);
        }
    }

    public function test_rechaza_el_rango_incompleto()
    {
        $this->actingAs($this->operador())
            ->get(self::RUTA . '?desde=2026-01-01')
            ->assertSessionHasErrors('hasta');
    }
}
