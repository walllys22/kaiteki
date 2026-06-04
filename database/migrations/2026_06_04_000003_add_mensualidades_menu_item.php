<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Shift orders 4+ to make room
        DB::table('menu_items')
            ->where('menu_id', 1)
            ->where('parent_id', null)
            ->where('order', '>=', 4)
            ->increment('order');

        DB::table('menu_items')->insert([
            'menu_id'    => 1,
            'title'      => 'Mensualidades',
            'url'        => '',
            'route'      => 'mensualidades.index',
            'target'     => '_self',
            'icon_class' => 'fa-solid fa-file-invoice-dollar',
            'color'      => null,
            'parent_id'  => null,
            'order'      => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('menu_items')->where('route', 'mensualidades.index')->delete();

        DB::table('menu_items')
            ->where('menu_id', 1)
            ->where('parent_id', null)
            ->where('order', '>=', 5)
            ->decrement('order');
    }
};
