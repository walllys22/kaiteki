<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $menuId = DB::table('menus')->where('name', 'admin')->value('id');

        if (!$menuId) {
            $menuId = DB::table('menus')->insertGetId([
                'name'       => 'admin',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        DB::table('menu_items')->updateOrInsert(
            ['route' => 'voyager.asistencias.index'],
            [
                'menu_id'    => $menuId,
                'title'      => 'Asistencias',
                'url'        => '',
                'target'     => '_self',
                'icon_class' => 'fa-solid fa-clipboard-user',
                'color'      => null,
                'parent_id'  => null,
                'order'      => 10,
                'parameters' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }

    public function down(): void
    {
        DB::table('menu_items')->where('route', 'voyager.asistencias.index')->delete();
    }
};
