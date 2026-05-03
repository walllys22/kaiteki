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

        

        // Peopple
        $permissions = [
            'browse_people' => 'Ver lista de personas',
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

        //Categorias
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

        //Ciudad
        $permissions = [
            'browse_ciudads' => 'Ver lista de ciudad',
            'read_ciudads' => 'Ver detalles de ciudad',
            'edit_ciudads' => 'Editar información de ciudad',
            'add_ciudads' => 'Agregar nuevas ciudad',
            'delete_ciudads' => 'Eliminar ciudad',
        ];

        foreach ($permissions as $key => $description) {
            Permission::firstOrCreate([
                'key'        => $key,
                'keyDescription'=> $description,
                'table_name' => 'ciudads',
                'tableDescription'=>'Ciudades'
            ]);
        }
     
        //Torneos
        $permissions = [
            'browse_torneos' => 'Ver lista de Torneos',
            'read_torneos' => 'Ver detalles de una Torneos',
            'edit_torneos' => 'Editar información de Torneos',
            'add_torneos' => 'Agregar nuevos Torneos',
            'delete_torneos' => 'Eliminar Torneos',
        ];

        foreach ($permissions as $key => $description) {
            Permission::firstOrCreate([
                'key'        => $key,
                'keyDescription'=> $description,
                'table_name' => 'torneos',
                'tableDescription'=>'Torneos',
            ]);
        }
     
        //Grado
        $permissions = [
            'browse_grados' => 'Ver lista de Grados',
            'read_grados' => 'Ver detalles de un Grado',
            'edit_grados' => 'Editar información de Grados',
            'add_grados' => 'Agregar nuevos Grados',
            'delete_grados' => 'Eliminar Grado',
        ];

        foreach ($permissions as $key => $description) {
            Permission::firstOrCreate([
                'key'        => $key,
                'keyDescription'=> $description,
                'table_name' => 'grados',
                'tableDescription'=>'grados'
            ]);
        }

        //Modalidades
        $permissions = [
            'browse_modalidas' => 'Ver lista de C',
            'read_modalidas' => 'Ver detalles de una persona',
            'edit_modalidas' => 'Editar información de C',
            'add_modalidas' => 'Agregar nuevas C',
            'delete_modalidas' => 'Eliminar C',
        ];

        foreach ($permissions as $key => $description) {
            Permission::firstOrCreate([
                'key'        => $key,
                'keyDescription'=> $description,
                'table_name' => 'modalidas',
                'tableDescription'=>'modalidas'
            ]);
        }
    
        //Katas
        $permissions = [
            'browse_katas' => 'Ver lista de C',
            'read_katas' => 'Ver detalles de una persona',
            'edit_katas' => 'Editar información de C',
            'add_katas' => 'Agregar nuevas C',
            'delete_katas' => 'Eliminar C',
        ];

        foreach ($permissions as $key => $description) {
            Permission::firstOrCreate([
                'key'        => $key,
                'keyDescription'=> $description,
                'table_name' => 'katas',
                'tableDescription'=>'katas'
            ]);
        }
        
        //Parentesco
        $permissions = [
            'browse_parentescos' => 'Ver lista de parentesco',
            'read_parentescos' => 'Ver detalles de un parentesco',
            'edit_parentescos' => 'Editar información de parentesco',
            'add_parentescos' => 'Agregar nuevos parentesco',
            'delete_parentescos' => 'Eliminar parentesco',
        ];

        foreach ($permissions as $key => $description) {
            Permission::firstOrCreate([
                'key'        => $key,
                'keyDescription'=> $description,
                'table_name' => 'parentescos',
                'tableDescription'=>'parentescos'
            ]);
        }

        //Dojo
        $permissions = [
            'browse_dojos' => 'Ver lista de C',
            'read_dojos' => 'Ver detalles de una persona',
            'edit_dojos' => 'Editar información de C',
            'add_dojos' => 'Agregar nuevas C',
            'delete_dojos' => 'Eliminar C',
        ];

        foreach ($permissions as $key => $description) {
            Permission::firstOrCreate([
                'key'        => $key,
                'keyDescription'=> $description,
                'table_name' => 'dojos',
                'tableDescription'=>'dojos'
            ]);
        }

        //Horarios
        $permissions = [
            'browse_horarios' => 'Ver lista de C',
            'read_horarios' => 'Ver detalles de una persona',
            'edit_horarios' => 'Editar información de C',
            'add_horarios' => 'Agregar nuevas C',
            'delete_horarios' => 'Eliminar C',
        ];

        foreach ($permissions as $key => $description) {
            Permission::firstOrCreate([
                'key'        => $key,
                'keyDescription'=> $description,
                'table_name' => 'horarios',
                'tableDescription'=>'horarios'
            ]);
        }

        //Alumnos
        $permissions = [
            'browse_alumnos' => 'Ver lista de alumnos',
            'read_alumnos' => 'Ver detalles de un alumnos',
            'edit_alumnos' => 'Editar información de alumnos',
            'add_alumnos' => 'Agregar nuevos alumnos',
            'delete_alumnos' => 'Eliminar alumnos',
        ];

        foreach ($permissions as $key => $description) {
            Permission::firstOrCreate([
                'key'        => $key,
                'keyDescription'=> $description,
                'table_name' => 'alumnos',
                'tableDescription'=>'Alumnos'
            ]);
        }

        $permissions = [
            'browse_asistencias' => 'Ver lista de asistencias',
            'read_asistencias' => 'Ver detalles de asistencias',
            'edit_asistencias' => 'Editar información de asistencias',
            'add_asistencias' => 'Agregar nuevas asistencias',
            'delete_asistencias' => 'Eliminar asistencias',
        ];

        foreach ($permissions as $key => $description) {
            Permission::firstOrCreate([
                'key'        => $key,
                'keyDescription'=> $description,
                'table_name' => 'asistencias',
                'tableDescription'=>'Asistencias'
            ]);
        }

        
    }
}