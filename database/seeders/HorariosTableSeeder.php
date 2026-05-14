<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class HorariosTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('horarios')->delete();
        
        \DB::table('horarios')->insert(array (
            0 => 
            array (
                'id' => 1,
                'dojo_id' => 3,
                'tipo' => 'Noche',
                'nombre' => '19:00 - 20:00',
                'status' => 1,
                'created_at' => '2026-05-06 15:34:29',
                'updated_at' => '2026-05-06 15:34:29',
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
                'dojo_id' => 1,
                'tipo' => 'Tarde',
                'nombre' => '16:00 - 17:00',
                'status' => 1,
                'created_at' => '2026-05-06 15:35:03',
                'updated_at' => '2026-05-06 15:35:03',
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
                'dojo_id' => 2,
                'tipo' => 'Tarde',
                'nombre' => '15:00 - 16:00',
                'status' => 1,
                'created_at' => '2026-05-06 15:43:33',
                'updated_at' => '2026-05-06 15:43:33',
                'registerUser_id' => 3,
                'registerRole' => 'administrador_dojo',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
        ));
        
        
    }
}