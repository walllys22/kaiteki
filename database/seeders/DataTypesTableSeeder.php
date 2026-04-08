<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DataTypesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('data_types')->delete();
        
        \DB::table('data_types')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'users',
                'slug' => 'users',
                'display_name_singular' => 'User',
                'display_name_plural' => 'Users',
                'icon' => 'voyager-person',
                'model_name' => 'TCG\\Voyager\\Models\\User',
                'policy_name' => 'TCG\\Voyager\\Policies\\UserPolicy',
                'controller' => 'TCG\\Voyager\\Http\\Controllers\\VoyagerUserController',
                'description' => NULL,
                'generate_permissions' => 1,
                'server_side' => 0,
                'details' => '{"order_column":null,"order_display_column":null,"order_direction":"desc","default_search_key":null,"scope":null}',
                'created_at' => '2024-10-18 14:28:26',
                'updated_at' => '2025-04-07 16:18:35',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'menus',
                'slug' => 'menus',
                'display_name_singular' => 'Menu',
                'display_name_plural' => 'Menus',
                'icon' => 'voyager-list',
                'model_name' => 'TCG\\Voyager\\Models\\Menu',
                'policy_name' => NULL,
                'controller' => '',
                'description' => '',
                'generate_permissions' => 1,
                'server_side' => 0,
                'details' => NULL,
                'created_at' => '2024-10-18 14:28:26',
                'updated_at' => '2024-10-18 14:28:26',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'roles',
                'slug' => 'roles',
                'display_name_singular' => 'Role',
                'display_name_plural' => 'Roles',
                'icon' => 'voyager-lock',
                'model_name' => 'TCG\\Voyager\\Models\\Role',
                'policy_name' => NULL,
                'controller' => 'TCG\\Voyager\\Http\\Controllers\\VoyagerRoleController',
                'description' => '',
                'generate_permissions' => 1,
                'server_side' => 0,
                'details' => NULL,
                'created_at' => '2024-10-18 14:28:26',
                'updated_at' => '2024-10-18 14:28:26',
            ),
            3 => 
            array (
                'id' => 8,
                'name' => 'people',
                'slug' => 'people',
                'display_name_singular' => 'Persona',
                'display_name_plural' => 'Personas',
                'icon' => 'fa-solid fa-person',
                'model_name' => 'App\\Models\\Person',
                'policy_name' => NULL,
                'controller' => NULL,
                'description' => NULL,
                'generate_permissions' => 1,
                'server_side' => 0,
                'details' => '{"order_column":null,"order_display_column":null,"order_direction":"asc","default_search_key":null,"scope":null}',
                'created_at' => '2025-04-07 09:43:00',
                'updated_at' => '2026-04-07 19:20:32',
            ),
            4 => 
            array (
                'id' => 9,
                'name' => 'ciudads',
                'slug' => 'ciudads',
                'display_name_singular' => 'Ciudad',
                'display_name_plural' => 'Ciudades',
                'icon' => 'fa-solid fa-city',
                'model_name' => 'App\\Models\\Ciudad',
                'policy_name' => NULL,
                'controller' => NULL,
                'description' => NULL,
                'generate_permissions' => 1,
                'server_side' => 0,
                'details' => '{"order_column":null,"order_display_column":null,"order_direction":"asc","default_search_key":null,"scope":null}',
                'created_at' => '2026-04-04 17:31:06',
                'updated_at' => '2026-04-04 17:32:35',
            ),
            5 => 
            array (
                'id' => 15,
                'name' => 'torneos',
                'slug' => 'torneos',
                'display_name_singular' => 'Torneo',
                'display_name_plural' => 'Torneos',
                'icon' => 'fa-solid fa-trophy',
                'model_name' => 'App\\Models\\Torneo',
                'policy_name' => NULL,
                'controller' => NULL,
                'description' => NULL,
                'generate_permissions' => 1,
                'server_side' => 0,
                'details' => '{"order_column":null,"order_display_column":null,"order_direction":"asc","default_search_key":null,"scope":null}',
                'created_at' => '2026-04-04 17:54:50',
                'updated_at' => '2026-04-04 18:05:34',
            ),
            6 => 
            array (
                'id' => 17,
                'name' => 'categorias',
                'slug' => 'categorias',
                'display_name_singular' => 'Categoria',
                'display_name_plural' => 'Categorias',
                'icon' => 'fa-solid fa-layer-group',
                'model_name' => 'App\\Models\\Categoria',
                'policy_name' => NULL,
                'controller' => NULL,
                'description' => NULL,
                'generate_permissions' => 1,
                'server_side' => 0,
                'details' => '{"order_column":null,"order_display_column":null,"order_direction":"asc","default_search_key":null,"scope":null}',
                'created_at' => '2026-04-04 23:21:22',
                'updated_at' => '2026-04-05 01:56:24',
            ),
            7 => 
            array (
                'id' => 18,
                'name' => 'grados',
                'slug' => 'grados',
                'display_name_singular' => 'Grado',
                'display_name_plural' => 'Grados',
                'icon' => 'fa-solid fa-bezier-curve',
                'model_name' => 'App\\Models\\Grado',
                'policy_name' => NULL,
                'controller' => NULL,
                'description' => NULL,
                'generate_permissions' => 1,
                'server_side' => 0,
                'details' => '{"order_column":null,"order_display_column":null,"order_direction":"asc","default_search_key":null}',
                'created_at' => '2026-04-05 00:44:14',
                'updated_at' => '2026-04-05 00:44:14',
            ),
            8 => 
            array (
                'id' => 19,
                'name' => 'modalidas',
                'slug' => 'modalidas',
                'display_name_singular' => 'Modalida',
                'display_name_plural' => 'Modalidades',
                'icon' => 'fa-brands fa-markdown',
                'model_name' => 'App\\Models\\Modalida',
                'policy_name' => NULL,
                'controller' => NULL,
                'description' => NULL,
                'generate_permissions' => 1,
                'server_side' => 0,
                'details' => '{"order_column":null,"order_display_column":null,"order_direction":"asc","default_search_key":null,"scope":null}',
                'created_at' => '2026-04-05 01:38:51',
                'updated_at' => '2026-04-05 01:42:43',
            ),
            9 => 
            array (
                'id' => 20,
                'name' => 'parentescos',
                'slug' => 'parentescos',
                'display_name_singular' => 'Parentesco',
                'display_name_plural' => 'Parentescos',
                'icon' => 'fa-solid fa-people-pulling',
                'model_name' => 'App\\Models\\Parentesco',
                'policy_name' => NULL,
                'controller' => NULL,
                'description' => NULL,
                'generate_permissions' => 1,
                'server_side' => 0,
                'details' => '{"order_column":null,"order_display_column":null,"order_direction":"asc","default_search_key":null,"scope":null}',
                'created_at' => '2026-04-05 09:33:01',
                'updated_at' => '2026-04-05 09:34:51',
            ),
            10 => 
            array (
                'id' => 21,
                'name' => 'katas',
                'slug' => 'katas',
                'display_name_singular' => 'Kata',
                'display_name_plural' => 'Katas',
                'icon' => 'fa-brands fa-korvue',
                'model_name' => 'App\\Models\\Kata',
                'policy_name' => NULL,
                'controller' => NULL,
                'description' => NULL,
                'generate_permissions' => 1,
                'server_side' => 0,
                'details' => '{"order_column":null,"order_display_column":null,"order_direction":"asc","default_search_key":null,"scope":null}',
                'created_at' => '2026-04-07 10:28:58',
                'updated_at' => '2026-04-07 10:37:38',
            ),
            11 => 
            array (
                'id' => 22,
                'name' => 'dojos',
                'slug' => 'dojos',
                'display_name_singular' => 'Dojo',
                'display_name_plural' => 'Dojos',
                'icon' => 'fa-solid fa-archway',
                'model_name' => 'App\\Models\\Dojo',
                'policy_name' => NULL,
                'controller' => NULL,
                'description' => NULL,
                'generate_permissions' => 1,
                'server_side' => 0,
                'details' => '{"order_column":null,"order_display_column":null,"order_direction":"asc","default_search_key":null,"scope":null}',
                'created_at' => '2026-04-07 11:56:15',
                'updated_at' => '2026-04-07 12:03:07',
            ),
            12 => 
            array (
                'id' => 23,
                'name' => 'horarios',
                'slug' => 'horarios',
                'display_name_singular' => 'Horario',
                'display_name_plural' => 'Horarios',
                'icon' => 'fa-solid fa-clock',
                'model_name' => 'App\\Models\\Horario',
                'policy_name' => NULL,
                'controller' => NULL,
                'description' => NULL,
                'generate_permissions' => 1,
                'server_side' => 0,
                'details' => '{"order_column":null,"order_display_column":null,"order_direction":"asc","default_search_key":null,"scope":null}',
                'created_at' => '2026-04-07 18:40:57',
                'updated_at' => '2026-04-07 18:43:09',
            ),
        ));
        
        
    }
}