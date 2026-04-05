<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Permission;

class PermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('permissions')->delete();
        
        Permission::firstOrCreate([
            'key'        => 'browse_admin',
            'keyDescription'=>'vista de acceso al sistema',
            'table_name' => 'admin',
            'tableDescription'=>'Panel del Sistema'
        ]);

        $keys = [
            // 'browse_admin',
            'browse_bread',
            'browse_database',
            'browse_media',
            'browse_compass',
            'browse_clear-cache',
        ];

        foreach ($keys as $key) {
            Permission::firstOrCreate([
                'key'        => $key,
                'table_name' => null,
            ]);
        }

        Permission::generateFor('menus');

        Permission::generateFor('roles');
        Permission::generateFor('permissions');
        Permission::generateFor('settings');

        Permission::generateFor('users');

        Permission::generateFor('posts');
        Permission::generateFor('categories');
        Permission::generateFor('pages');

        

        // C
        $permissions = [
            'browse_people' => 'Ver lista de C',
            'read_people' => 'Ver detalles de una persona',
            'edit_people' => 'Editar información de personas',
            'add_people' => 'Agregar nuevas personas',
            'delete_people' => 'Eliminar personas',
        ];

        foreach ($permissions as $key => $description) {
            Permission::firstOrCreate([
                'key'        => $key,
                'keyDescription'=> $description,
                'table_name' => 'people',
                'tableDescription'=>'Personas'
            ]);
        }

        $permissions = [
            'browse_categorias' => 'Ver lista de Categorias',
            'read_categorias' => 'Ver detalles de una Categoria',
            'edit_categorias' => 'Editar información de Categorias',
            'add_categorias' => 'Agregar nuevas Categorias',
            'delete_categorias' => 'Eliminar Categorias',
        ];

        foreach ($permissions as $key => $description) {
            Permission::firstOrCreate([
                'key'        => $key,
                'keyDescription'=> $description,
                'table_name' => 'categorias',
                'tableDescription'=>'Categorias'
            ]);
        }

        
        $permissions = [
            'browse_ciudads' => 'Ver lista de C',
            'read_ciudads' => 'Ver detalles de una persona',
            'edit_ciudads' => 'Editar información de C',
            'add_ciudads' => 'Agregar nuevas C',
            'delete_ciudads' => 'Eliminar C',
        ];

        foreach ($permissions as $key => $description) {
            Permission::firstOrCreate([
                'key'        => $key,
                'keyDescription'=> $description,
                'table_name' => 'ciudads',
                'tableDescription'=>'Ciudades'
            ]);
        }
     
        $permissions = [
            'browse_Torneos' => 'Ver lista de C',
            'read_Torneos' => 'Ver detalles de una persona',
            'edit_Torneos' => 'Editar información de C',
            'add_Torneos' => 'Agregar nuevas C',
            'delete_Torneos' => 'Eliminar C',
        ];

        foreach ($permissions as $key => $description) {
            Permission::firstOrCreate([
                'key'        => $key,
                'keyDescription'=> $description,
                'table_name' => 'Torneos',
                'tableDescription'=>'Ciudades'
            ]);
        }
     


        
        
    }
}