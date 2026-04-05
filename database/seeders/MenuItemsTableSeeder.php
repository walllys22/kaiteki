<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MenuItemsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('menu_items')->delete();
        
        \DB::table('menu_items')->insert(array (
            0 => 
            array (
                'id' => 1,
                'menu_id' => 1,
                'title' => 'Inicio',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'fa-solid fa-house',
                'color' => '#000000',
                'parent_id' => NULL,
                'order' => 1,
                'created_at' => '2024-10-18 14:28:27',
                'updated_at' => '2024-10-18 16:31:13',
                'route' => 'voyager.dashboard',
                'parameters' => 'null',
            ),
            1 => 
            array (
                'id' => 2,
                'menu_id' => 1,
                'title' => 'Media',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'voyager-images',
                'color' => NULL,
                'parent_id' => 5,
                'order' => 5,
                'created_at' => '2024-10-18 14:28:27',
                'updated_at' => '2024-10-18 16:40:50',
                'route' => 'voyager.media.index',
                'parameters' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'menu_id' => 1,
                'title' => 'Usuarios',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'voyager-person',
                'color' => '#000000',
                'parent_id' => 14,
                'order' => 1,
                'created_at' => '2024-10-18 14:28:27',
                'updated_at' => '2024-10-18 16:33:38',
                'route' => 'voyager.users.index',
                'parameters' => 'null',
            ),
            3 => 
            array (
                'id' => 4,
                'menu_id' => 1,
                'title' => 'Roles',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'voyager-lock',
                'color' => NULL,
                'parent_id' => 14,
                'order' => 2,
                'created_at' => '2024-10-18 14:28:27',
                'updated_at' => '2026-04-04 22:52:50',
                'route' => 'voyager.roles.index',
                'parameters' => NULL,
            ),
            4 => 
            array (
                'id' => 5,
                'menu_id' => 1,
                'title' => 'Herramientas',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'voyager-tools',
                'color' => '#000000',
                'parent_id' => NULL,
                'order' => 5,
                'created_at' => '2024-10-18 14:28:27',
                'updated_at' => '2026-04-05 00:07:11',
                'route' => NULL,
                'parameters' => '',
            ),
            5 => 
            array (
                'id' => 6,
                'menu_id' => 1,
                'title' => 'Menu Builder',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'voyager-list',
                'color' => NULL,
                'parent_id' => 5,
                'order' => 1,
                'created_at' => '2024-10-18 14:28:27',
                'updated_at' => '2024-10-18 16:33:17',
                'route' => 'voyager.menus.index',
                'parameters' => NULL,
            ),
            6 => 
            array (
                'id' => 7,
                'menu_id' => 1,
                'title' => 'Database',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'voyager-data',
                'color' => NULL,
                'parent_id' => 5,
                'order' => 2,
                'created_at' => '2024-10-18 14:28:27',
                'updated_at' => '2024-10-18 16:40:50',
                'route' => 'voyager.database.index',
                'parameters' => NULL,
            ),
            7 => 
            array (
                'id' => 8,
                'menu_id' => 1,
                'title' => 'Compass',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'voyager-compass',
                'color' => NULL,
                'parent_id' => 5,
                'order' => 3,
                'created_at' => '2024-10-18 14:28:27',
                'updated_at' => '2024-10-18 16:40:50',
                'route' => 'voyager.compass.index',
                'parameters' => NULL,
            ),
            8 => 
            array (
                'id' => 9,
                'menu_id' => 1,
                'title' => 'BREAD',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'voyager-bread',
                'color' => NULL,
                'parent_id' => 5,
                'order' => 4,
                'created_at' => '2024-10-18 14:28:27',
                'updated_at' => '2024-10-18 16:40:50',
                'route' => 'voyager.bread.index',
                'parameters' => NULL,
            ),
            9 => 
            array (
                'id' => 10,
                'menu_id' => 1,
                'title' => 'Configuración',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'voyager-settings',
                'color' => '#000000',
                'parent_id' => NULL,
                'order' => 6,
                'created_at' => '2024-10-18 14:28:27',
                'updated_at' => '2026-04-05 00:07:11',
                'route' => 'voyager.settings.index',
                'parameters' => 'null',
            ),
            10 => 
            array (
                'id' => 14,
                'menu_id' => 1,
                'title' => 'Seguridad',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'fa-solid fa-lock',
                'color' => '#000000',
                'parent_id' => NULL,
                'order' => 4,
                'created_at' => '2024-10-18 16:33:05',
                'updated_at' => '2026-04-05 00:07:11',
                'route' => NULL,
                'parameters' => '',
            ),
            11 => 
            array (
                'id' => 15,
                'menu_id' => 1,
                'title' => 'Limpiar cache',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'fa-solid fa-broom',
                'color' => '#000000',
                'parent_id' => NULL,
                'order' => 7,
                'created_at' => '2024-12-09 12:33:25',
                'updated_at' => '2026-04-05 00:07:11',
                'route' => 'clear.cache',
                'parameters' => NULL,
            ),
            12 => 
            array (
                'id' => 16,
                'menu_id' => 1,
                'title' => 'Administración',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'fa-regular fa-folder-open',
                'color' => '#000000',
                'parent_id' => NULL,
                'order' => 2,
                'created_at' => '2025-02-10 15:42:49',
                'updated_at' => '2026-04-04 18:06:39',
                'route' => NULL,
                'parameters' => '',
            ),
            13 => 
            array (
                'id' => 18,
                'menu_id' => 1,
                'title' => 'Personas',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'fa-solid fa-person',
                'color' => NULL,
                'parent_id' => 16,
                'order' => 2,
                'created_at' => '2025-04-07 09:43:00',
                'updated_at' => '2026-04-04 23:33:06',
                'route' => 'voyager.people.index',
                'parameters' => NULL,
            ),
            14 => 
            array (
                'id' => 20,
                'menu_id' => 1,
                'title' => 'Ciudades',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'fa-solid fa-city',
                'color' => '#000000',
                'parent_id' => 27,
                'order' => 1,
                'created_at' => '2026-04-04 17:31:06',
                'updated_at' => '2026-04-05 00:07:27',
                'route' => 'voyager.ciudads.index',
                'parameters' => 'null',
            ),
            15 => 
            array (
                'id' => 23,
                'menu_id' => 1,
                'title' => 'Torneos',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'fa-solid fa-trophy',
                'color' => NULL,
                'parent_id' => 27,
                'order' => 2,
                'created_at' => '2026-04-04 17:54:51',
                'updated_at' => '2026-04-05 00:07:27',
                'route' => 'voyager.torneos.index',
                'parameters' => NULL,
            ),
            16 => 
            array (
                'id' => 26,
                'menu_id' => 1,
                'title' => 'Categorias',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'fa-solid fa-layer-group',
                'color' => NULL,
                'parent_id' => 16,
                'order' => 1,
                'created_at' => '2026-04-04 23:21:22',
                'updated_at' => '2026-04-04 23:33:06',
                'route' => 'voyager.categorias.index',
                'parameters' => NULL,
            ),
            17 => 
            array (
                'id' => 27,
                'menu_id' => 1,
                'title' => 'Parametros',
                'url' => '',
                'target' => '_self',
                'icon_class' => 'voyager-params',
                'color' => '#000000',
                'parent_id' => NULL,
                'order' => 3,
                'created_at' => '2026-04-05 00:06:46',
                'updated_at' => '2026-04-05 00:07:10',
                'route' => NULL,
                'parameters' => '',
            ),
        ));
        
        
    }
}