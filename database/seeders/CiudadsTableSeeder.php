<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CiudadsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('ciudads')->delete();
        
        \DB::table('ciudads')->insert(array (
            0 => 
            array (
                'id' => 1,
                'nombre' => 'Trinidad',
                'status' => 1,
                'created_at' => '2026-04-23 07:29:13',
                'updated_at' => '2026-04-23 07:29:13',
                'registerUser_id' => NULL,
                'registerRole' => NULL,
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            1 => 
            array (
                'id' => 2,
                'nombre' => 'Santa Cruz',
                'status' => 1,
                'created_at' => '2026-04-23 07:29:58',
                'updated_at' => '2026-04-23 07:29:58',
                'registerUser_id' => NULL,
                'registerRole' => NULL,
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
            2 => 
            array (
                'id' => 3,
                'nombre' => 'Cochabamba',
                'status' => 1,
                'created_at' => '2026-04-23 07:30:07',
                'updated_at' => '2026-04-23 07:30:07',
                'registerUser_id' => NULL,
                'registerRole' => NULL,
                'deleted_at' => NULL,
                'deleteUser_id' => NULL,
                'deleteRole' => NULL,
                'deleteObservation' => NULL,
            ),
        ));
        
        
    }
}