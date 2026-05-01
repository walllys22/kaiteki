<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('menu_items')->insert([
            'menu_id'    => 1,
            'title'      => 'Asistencias',
            'url'        => '',
            'route'      => 'voyager.asistencias.index',
            'target'     => '_self',
            'icon_class' => 'fa-solid fa-clipboard-user',
            'color'      => null,
            'parent_id'  => null,
            'order'      => 10,
            'parameters' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('menu_items')->where('route', 'voyager.asistencias.index')->delete();
    }
};
