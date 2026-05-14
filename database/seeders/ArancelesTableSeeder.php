<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ArancelesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('aranceles')->delete();
        
        \DB::table('aranceles')->insert(array (
            0 => 
            array (
                'id' => 1,
                'grado_id' => 10,
                'dojo_id' => 3,
                'tipo' => 'Repaso',
                'precio' => '10.00',
                'observacion' => NULL,
                'status' => 1,
                'created_at' => '2026-05-06 15:55:32',
                'updated_at' => '2026-05-06 15:55:32',
                'registerUser_id' => 2,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'grado_id' => 10,
                'dojo_id' => 2,
                'tipo' => 'Repaso',
                'precio' => '15.00',
                'observacion' => NULL,
                'status' => 1,
                'created_at' => '2026-05-06 15:55:42',
                'updated_at' => '2026-05-06 15:55:42',
                'registerUser_id' => 2,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'grado_id' => 10,
                'dojo_id' => 3,
                'tipo' => 'Examen',
                'precio' => '150.00',
                'observacion' => NULL,
                'status' => 1,
                'created_at' => '2026-05-06 15:56:13',
                'updated_at' => '2026-05-06 15:56:13',
                'registerUser_id' => 2,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            3 => 
            array (
                'id' => 4,
                'grado_id' => 10,
                'dojo_id' => 2,
                'tipo' => 'Examen',
                'precio' => '200.00',
                'observacion' => NULL,
                'status' => 1,
                'created_at' => '2026-05-06 15:56:26',
                'updated_at' => '2026-05-06 15:56:26',
                'registerUser_id' => 2,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            4 => 
            array (
                'id' => 5,
                'grado_id' => 2,
                'dojo_id' => 3,
                'tipo' => 'Repaso',
                'precio' => '10.00',
                'observacion' => NULL,
                'status' => 1,
                'created_at' => '2026-05-06 19:47:36',
                'updated_at' => '2026-05-06 19:47:36',
                'registerUser_id' => 2,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            5 => 
            array (
                'id' => 6,
                'grado_id' => 2,
                'dojo_id' => 3,
                'tipo' => 'Examen',
                'precio' => '150.00',
                'observacion' => NULL,
                'status' => 1,
                'created_at' => '2026-05-06 19:47:52',
                'updated_at' => '2026-05-06 19:47:52',
                'registerUser_id' => 2,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            6 => 
            array (
                'id' => 7,
                'grado_id' => 3,
                'dojo_id' => 3,
                'tipo' => 'Repaso',
                'precio' => '10.00',
                'observacion' => NULL,
                'status' => 1,
                'created_at' => '2026-05-06 19:59:34',
                'updated_at' => '2026-05-06 19:59:34',
                'registerUser_id' => 2,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            7 => 
            array (
                'id' => 8,
                'grado_id' => 3,
                'dojo_id' => 3,
                'tipo' => 'Examen',
                'precio' => '150.00',
                'observacion' => NULL,
                'status' => 1,
                'created_at' => '2026-05-06 19:59:54',
                'updated_at' => '2026-05-06 19:59:54',
                'registerUser_id' => 2,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            8 => 
            array (
                'id' => 9,
                'grado_id' => 11,
                'dojo_id' => 7,
                'tipo' => 'Examen',
                'precio' => '2100.00',
                'observacion' => NULL,
                'status' => 1,
                'created_at' => '2026-05-06 20:10:37',
                'updated_at' => '2026-05-06 20:10:37',
                'registerUser_id' => 2,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            9 => 
            array (
                'id' => 10,
                'grado_id' => 1,
                'dojo_id' => 3,
                'tipo' => 'Repaso',
                'precio' => '10.00',
                'observacion' => NULL,
                'status' => 1,
                'created_at' => '2026-05-12 07:28:07',
                'updated_at' => '2026-05-12 07:28:07',
                'registerUser_id' => 4,
                'registerRole' => 'administrador_dojo',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            10 => 
            array (
                'id' => 11,
                'grado_id' => 1,
                'dojo_id' => 3,
                'tipo' => 'Examen',
                'precio' => '150.00',
                'observacion' => NULL,
                'status' => 1,
                'created_at' => '2026-05-12 07:28:17',
                'updated_at' => '2026-05-12 07:28:17',
                'registerUser_id' => 4,
                'registerRole' => 'administrador_dojo',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            11 => 
            array (
                'id' => 12,
                'grado_id' => 4,
                'dojo_id' => 3,
                'tipo' => 'Repaso',
                'precio' => '10.00',
                'observacion' => NULL,
                'status' => 1,
                'created_at' => '2026-05-12 15:42:36',
                'updated_at' => '2026-05-12 15:42:36',
                'registerUser_id' => 2,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            12 => 
            array (
                'id' => 13,
                'grado_id' => 4,
                'dojo_id' => 3,
                'tipo' => 'Examen',
                'precio' => '150.00',
                'observacion' => NULL,
                'status' => 1,
                'created_at' => '2026-05-12 15:42:56',
                'updated_at' => '2026-05-12 15:42:56',
                'registerUser_id' => 2,
                'registerRole' => 'administrador',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
        ));
        
        
    }
}