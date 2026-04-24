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
                'tipo' => 'Mañana',
                'nombre' => '09:00 - 10:00',
                'created_at' => '2026-04-23 20:17:26',
                'updated_at' => '2026-04-23 20:17:26',
                'deleted_at' => NULL,
                'status' => 1,
                'registerUser_id' => 1,
                'registerRole' => 'admin',
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
                'dojo_id' => 2,
            ),
            1 => 
            array (
                'id' => 2,
                'tipo' => 'Tarde',
                'nombre' => '15:00 - 16:00',
                'created_at' => '2026-04-23 20:19:22',
                'updated_at' => '2026-04-23 20:19:22',
                'deleted_at' => NULL,
                'status' => 1,
                'registerUser_id' => 3,
                'registerRole' => 'administrador',
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
                'dojo_id' => 1,
            ),
        ));
        
        
    }
}