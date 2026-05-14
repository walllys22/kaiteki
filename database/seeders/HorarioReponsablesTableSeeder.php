<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class HorarioReponsablesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('horario_reponsables')->delete();
        
        \DB::table('horario_reponsables')->insert(array (
            0 => 
            array (
                'id' => 1,
                'horario_id' => 1,
                'person_id' => 7,
                'observacion' => NULL,
                'status' => 1,
                'created_at' => '2026-05-12 05:50:10',
                'updated_at' => '2026-05-12 05:50:10',
                'registerUser_id' => 4,
                'registerRole' => 'administrador_dojo',
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
        ));
        
        
    }
}